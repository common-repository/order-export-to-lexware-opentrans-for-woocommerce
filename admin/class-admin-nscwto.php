<?php

class Admin_nscwto
{

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, NSC_WTO_PLUGIN_URL . 'admin/css/woo-order-to-lexware-nscwto-admin.css', array(), NSC_WTO_VERSION, 'all');
        wp_enqueue_style('admin_styles_vanillaSelectBox_nsc_wto', NSC_WTO_PLUGIN_URL . 'admin/vanillaSelectBox/vanillaSelectBox.css', array(), NSC_WTO_VERSION);

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, NSC_WTO_PLUGIN_URL . 'admin/js/woo-order-to-lexware-nscwto-admin.js', array('jquery'), NSC_WTO_VERSION, false);
        wp_enqueue_script('vanillaSelectBox_nsc_wto', NSC_WTO_PLUGIN_URL . 'admin/vanillaSelectBox/vanillaSelectBox.js', array(), NSC_WTO_VERSION);

    }

    public function add_admin_menu()
    {
        $plugin_configs = new plugin_configs_nscwto;
        $configs = $plugin_configs->return_plugin_configs_without_db_settings();
        add_submenu_page('woocommerce', __($configs->settings_page_configs->page_title, 'order-to-lexware-nscwto'), __($configs->settings_page_configs->menu_title, 'order-to-lexware-nscwto'), $configs->settings_page_configs->capability, $configs->plugin_slug, array($this, "create_admin_page"));
    }

    public function create_admin_page()
    {
        $plugin_configs = new plugin_configs_nscwto;
        $form_fields = new html_formfields_nscwto;
        $configs = $plugin_configs->return_plugin_settings();
        require NSC_WTO_PLUGIN_DIR . "/admin/partials/display-nscwto.php";
    }

    public function save_settings()
    {
        $plugin_configs = new plugin_configs_nscwto();
        $plugin_configs->save_settings();
    }

    public function download_order_export()
    {

        if (empty($_POST["option_page"])) {
            return;
        }

        if (empty($_POST["submit"])) {
            return;
        }

        if ($_POST["option_page"] !== "woo-order-export-to-lexware-nscwtodownload") {
            return;
        }

        $validation = new input_validation_nscwto;
        $result = false;

        if (empty($_POST['nscwto_order_numbers_list']) && empty($_POST['nscwto_start_ordernummer']) && empty($_POST['nscwto_start_date'])) {
            $validation->return_errors_obj()->set_admin_error(__("All fields are empty. Please specify the orders you want to export."));
        }

        if ($result === false && !empty($_POST['nscwto_order_numbers_list']) && $validation->validate_field_custom_save('integer_number_list', $_POST['nscwto_order_numbers_list']) !== null) {
            $order_numbers = $validation->validate_field_custom_save("integer_number_list", $_POST['nscwto_order_numbers_list']);
            $download_order = new download_order_nscwto;
            $result = $download_order->download_by_specific_ordernumbers_nscwto($order_numbers);
        }

        if ($result === false && !empty($_POST['nscwto_start_ordernummer']) && $validation->validate_field_custom_save("integer", $_POST['nscwto_start_ordernummer']) !== null) {
            $order_start_nummer = $validation->validate_field_custom_save("integer", $_POST['nscwto_start_ordernummer']);
            $download_order = new download_order_nscwto;
            $result = $download_order->download_by_ordernumber_nscwto($order_start_nummer);
        }

        if ($result === false && !empty($_POST['nscwto_start_date']) && $validation->validate_field_custom_save('validateDate', $_POST['nscwto_start_date']) !== null) {
            $order_start_date = $validation->validate_field_custom_save("validateDate", $_POST['nscwto_start_date']);
            $download_order = new download_order_nscwto;
            $result = $download_order->download_by_date_nscwto($order_start_date);
        }

        if (!empty($result)) {
            $validation->return_errors_obj()->set_admin_error($result);
        }

        $validation->return_errors_obj()->display_errors();
    }
}
