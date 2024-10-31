<?php

class plugin_configs_nscwto
{
    private $configs_file;
    private $configs_as_object;
    private $configs_as_object_without_db;
    private $settings_array;
    private $settings_string;
    private $active_tab;

    public function return_plugin_settings()
    {
        if (empty($this->configs_as_object)) {
            $this->configs_as_object = $this->return_plugin_configs_without_db_settings();
            $this->add_current_setting_values();
            $this->add_html_description_templates();
            $this->configs_as_object = apply_filters('plugin_configs_as_an_object_with_setting_values_nscwto', $this->configs_as_object);
        }
        return $this->configs_as_object;
    }

    public function plugin_prefix()
    {
        $this->return_plugin_configs_without_db_settings();
        return $this->configs_as_object_without_db->plugin_prefix;
    }

    public function return_settings_field_default_value($searched_field_slug)
    {
        $settings_field = $this->return_settings_field($searched_field_slug);
        return $settings_field->pre_selected_value;
    }

    public function replace_variables_in_config($varname, $replace_value)
    {
        $configs = $this->return_plugin_settings();
        $configs_string = json_encode($configs);
        $configs_string = str_replace("{{" . $varname . "}}", $replace_value, $configs_string);
        $this->configs_as_object = json_decode($configs_string);
    }

    public function return_plugin_configs_without_db_settings()
    {
        if (empty($this->configs_as_object_without_db)) {
            $this->configs_as_object_without_db = $this->return_configs_as_object();
            if (empty($this->configs_as_object_without_db)) {
                throw new Exception($this->configs_file . " was not readable. Make sure it contains valid json.");
            }
        }
        return $this->configs_as_object_without_db;
    }

    public function save_settings()
    {

        $configs = $this->return_configs_as_object();
        if (!isset($_GET["page"]) || $_GET["page"] !== $configs->plugin_slug) {
            return false;
        }

        if (isset($_POST['submit']) === false) {
            return false;
        }

        $this->return_plugin_configs_without_db_settings();
        $this->get_settings_array();

        $tabs = $this->configs_as_object_without_db->setting_page_fields->tabs;
        $plugin_prefix = $this->configs_as_object_without_db->plugin_prefix;
        $validate = new input_validation_nscwto;
        $settings_updated = false;

        foreach ($tabs as $tab_index => $tab) {
            foreach ($tab->tabfields as $tabfield_index => $tabfield) {
                $tabfield_slug = $plugin_prefix . $tabfield->field_slug;
                if ($tabfield->save_in_db != true || (!isset($_POST[$tabfield_slug]) && !isset($_POST[$tabfield_slug . "_hidden"]))) {
                    continue;
                }

                if ($tabfield->type === "multiselect" && isset($_POST[$tabfield_slug])) {
                    $values = $_POST[$tabfield_slug];
                    $post_value = array();
                    foreach ($values as $value) {
                        $post_value[] = $value;
                    }
                } else {
                    $hidden_post_value = isset($_POST[$tabfield_slug . "_hidden"]) ? $_POST[$tabfield_slug . "_hidden"] : "";
                    $post_value = isset($_POST[$tabfield_slug]) ? $_POST[$tabfield_slug] : $hidden_post_value;
                }

                $new_value = $validate->validate_field_custom_save($tabfield->extra_validation_name, $post_value);
                $settings_updated = $this->update_setting($tabfield->field_slug, $new_value, $settings_updated);
            }
        }

        if ($settings_updated) {
            $settings_updated = $this->save_settings_to_db();
        }

        $validate->return_errors_obj()->display_errors();

        if ($settings_updated === true) {
            return true;
        }
        return false;
    }

    private function update_setting($slug, $new_value, $settings_updated)
    {
        if ($new_value !== null) {
            $this->settings_array[$slug] = $new_value;
            return true;
        }
        return $settings_updated;
    }

    private function save_settings_to_db()
    {
        check_admin_referer($_POST['option_page'] . "-options");

        if (current_user_can("view_woocommerce_reports") === false) {
            return false;
        }

        $this->settings_array["plugin_version"] = NSC_WTO_VERSION;
        $settings_array = $this->settings_array;
        $settings_array = apply_filters('settings_array_before_save_nsc_wto', $settings_array);
        $settings_string = json_encode($settings_array);
        return update_option($this->plugin_prefix() . 'plugin_settings', $settings_string);
    }

    private function return_settings_field($searched_field_slug)
    {
        $this->return_plugin_settings();
        foreach ($this->configs_as_object->setting_page_fields->tabs as $tab) {
            $number_of_fields = count($tab->tabfields);
            for ($i = 0; $i < $number_of_fields; $i++) {
                if ($tab->tabfields[$i]->field_slug == $searched_field_slug) {
                    return $tab->tabfields[$i];
                }
            }
        }
    }

    private function return_configs_as_object()
    {
        $this->configs_file = NSC_WTO_PLUGIN_DIR . "/plugin-configs.json";
        $configs = file_get_contents($this->configs_file);
        $configs = json_decode($configs);
        $configs = apply_filters('plugin_configs_as_an_object_without_db_nscwto', $configs);
        if (empty($configs)) {
            throw new Exception($this->configs_file . " was not readable. Make sure it contains valid json.");
        }
        return $configs;
    }

    private function get_active_tab()
    {
        $this->active_tab = "";
        if (isset($_GET["tab"])) {
            $this->active_tab = sanitize_text_field($_GET["tab"]);
        } else {
            $this->active_tab = $this->configs_as_object->setting_page_fields->tabs[0]->tab_slug;
        }
    }

    private function add_html_description_templates()
    {
        $number_of_tabs = count($this->configs_as_object->setting_page_fields->tabs);
        for ($t = 0; $t < $number_of_tabs; $t++) {
            $this->configs_as_object->setting_page_fields->tabs[$t]->tab_description = $this->get_tab_description_tipps_template($t, "tab_description");
            $this->configs_as_object->setting_page_fields->tabs[$t]->tab_tipps = $this->get_tab_description_tipps_template($t, "tab_tipps");
        }
    }

    private function get_tab_description_tipps_template($tab_index, $type)
    {
        // TODO: needed for premium add-on versions without this tab <= 1.2.1
        if (isset($this->configs_as_object->setting_page_fields->tabs[$tab_index]->$type) === false) {
            return "";
        }
        if (strpos($this->configs_as_object->setting_page_fields->tabs[$tab_index]->$type, ".html") === false ||
            !file_exists(NSC_WTO_PLUGIN_DIR . "/admin/tpl/" . $this->configs_as_object->setting_page_fields->tabs[$tab_index]->$type)) {
            return $this->configs_as_object->setting_page_fields->tabs[$tab_index]->$type;
        }
        return file_get_contents(NSC_WTO_PLUGIN_DIR . "/admin/tpl/" . $this->configs_as_object->setting_page_fields->tabs[$tab_index]->$type);
    }

    // this fuctions gets the value saved in wordpress db using get_option
    // and adds it to the settings object in the pre_selected_value field.
    // if no value is set it sets the default value from settings file.
    private function add_current_setting_values()
    {
        $this->get_active_tab();
        $translateable_fields = array('helpertext', 'name');
        $translateable_tab_fields = array('tabname', 'tab_description', "tab_tipps", "button_save_text");
        $this->configs_as_object->setting_page_fields->active_tab_slug = $this->active_tab;
        $number_of_tabs = count($this->configs_as_object->setting_page_fields->tabs);
        for ($t = 0; $t < $number_of_tabs; $t++) {
            $number_of_fields_in_this_tab = count($this->configs_as_object->setting_page_fields->tabs[$t]->tabfields);
            $this->configs_as_object->setting_page_fields->tabs[$t]->active = false;
            foreach ($translateable_tab_fields as $tab_field) {
                $translatedtabValue = __($this->configs_as_object->setting_page_fields->tabs[$t]->{$tab_field}, 'order-to-lexware-nscwto');
                $translatedtabValue = apply_filters('translated_value_nscwto', $translatedtabValue);
                $this->configs_as_object->setting_page_fields->tabs[$t]->{$tab_field} = $translatedtabValue;
            }
            if ($this->active_tab == $this->configs_as_object->setting_page_fields->tabs[$t]->tab_slug) {
                $this->configs_as_object->setting_page_fields->tabs[$t]->active = true;
                $this->configs_as_object->setting_page_fields->active_tab_index = $t;
            }
            for ($f = 0; $f < $number_of_fields_in_this_tab; $f++) {
                $default_value = $this->configs_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->pre_selected_value;
                $field_slug_without_prefix = $this->configs_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->field_slug;
                $this->configs_as_object->setting_page_fields->tabs[$t]->tabfields[$f] = apply_filters('tab_field_add_current_setting_values_nsc_wto', $this->configs_as_object->setting_page_fields->tabs[$t]->tabfields[$f]);
                if ($this->configs_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->save_in_db === true) {
                    $this->configs_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->pre_selected_value = $this->get_setting($field_slug_without_prefix, $default_value);
                };
                foreach ($translateable_fields as $field) {
                    $translatedFieldValue = __($this->configs_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->{$field}, 'order-to-lexware-nscwto');
                    $translatedFieldValue = apply_filters('translated_value_nscwto', $translatedFieldValue);
                    $this->configs_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->{$field} = $translatedFieldValue;

                }
            }
        }
    }

    public function get_setting($slug, $default_value)
    {
        $this->get_settings_array();
        $settings_array = $this->settings_array;
        $settings_array = apply_filters('settings_array_before_get_setting_nsc_wto', $settings_array, $slug, $default_value);
        if (isset($settings_array[$slug])) {
            return $settings_array[$slug];
        }
        return $default_value;
    }

    public function get_settings_array()
    {
        if (empty($this->settings_array)) {
            $settings_array = $this->initialise_settings();
            $settings_array = apply_filters('filter_settings_array_init_nscwto', $settings_array);
            $this->settings_array = $settings_array;
        }
        return $this->settings_array;
    }

    private function initialise_settings()
    {
        $banner_config_string = $this->read_settings_from_db();

        // try to get default, if non default is not set.
        if (empty($banner_config_string)) {
            $banner_config_string = '{}';
        }

        return json_decode($banner_config_string, true);
    }

    private function read_settings_from_db()
    {
        $settings_string = get_option($this->plugin_prefix() . 'plugin_settings', '{}');
        $validate = new input_validation_nscwto;
        $settings_string = $validate->validate_field_custom_save("check_valid_json_string", $settings_string);
        return $settings_string;
    }

}
