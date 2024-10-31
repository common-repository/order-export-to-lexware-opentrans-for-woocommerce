<?php

class html_formfields_nscwto
{
    private $field;
    private $prefix;

    public function return_form_field($field, $prefix)
    {
        $this->field = $field;
        $this->prefix = $prefix;
        switch ($this->field->type) {
            case "checkbox":
                return $this->create_checkbox();
                break;
            case "textarea":
                return $this->create_textarea();
                break;
            case "text":
                return $this->create_text();
                break;
            case "longtext":
                return $this->create_text("long");
                break;
            case "select":
                return $this->create_select();
                break;
            case "radio":
                return $this->create_radio();
                break;
            case "hidden":
                return $this->create_hidden_field();
                break;
            case "date":
                return $this->create_date();
                break;
            case "multiselect":
                return $this->create_multiselect();
                break;
            default:
                return esc_attr($this->field->pre_selected_value);
                break;
        }
    }

    private function create_checkbox()
    {
        $checkbox = '<input ' . esc_attr($this->is_disabled($this->field)) . ' id="ff_' . esc_attr($this->prefix . $this->field->field_slug) . '" type="checkbox" name="' . esc_attr($this->prefix . $this->field->field_slug) . '" value="1" ' . checked(1, esc_attr($this->field->pre_selected_value), false) . '>';
        if (empty($this->is_disabled($this->field)) === true) {
            $checkbox = '<input type="hidden" name="' . esc_attr($this->prefix . $this->field->field_slug) . '_hidden" value="0"/>' . $checkbox;
        }
        return '<label>' . $checkbox . '</label>';
    }

    private function create_date()
    {
        return '<label><input id="ff_' . esc_attr($this->prefix . $this->field->field_slug) . '" type="date" name="' . esc_attr($this->prefix . $this->field->field_slug) . '" value="0" ' . esc_attr($this->is_disabled($this->field)) . '></label>';
    }

    private function create_textarea()
    {
        return '<label><textarea ' . esc_attr($this->is_disabled($this->field)) . '  id="ff_' . esc_attr($this->prefix . $this->field->field_slug) . '" cols="120"  rows="20" class="large-text code" type="textarea">' . $this->convert_to_string(esc_attr($this->field->pre_selected_value)) . '</textarea></label>';
    }

    private function create_hidden_field()
    {
        return "<input type='hidden'  id='ff_" . esc_attr($this->prefix . $this->field->field_slug) . "' name='" . esc_attr($this->prefix . $this->field->field_slug) . "_hidden' value='" . $this->convert_to_string(esc_attr($this->field->pre_selected_value)) . "'/>";
    }

    private function create_text($length = "short")
    {
        $size = 20;
        if ($length == "long") {
            $size = 50;
        }
        return '<label><input ' . esc_attr($this->is_disabled($this->field)) . ' id="ff_' . esc_attr($this->prefix . $this->field->field_slug) . '" type="text"  name="' . esc_attr($this->prefix . $this->field->field_slug) . '" size="' . esc_attr($size) . '" maxlength="200" value="' . esc_attr($this->field->pre_selected_value) . '"></label>';
    }

    private function create_select()
    {

        $html = '<select ' . esc_attr($this->is_disabled($this->field)) . '  id="ff_' . esc_attr($this->prefix . $this->field->field_slug) . '"  name="' . esc_attr($this->prefix . $this->field->field_slug) . '">';
        foreach ($this->field->selectable_values as $selectable_value) {
            $select = "";
            if ($selectable_value->value == esc_attr($this->field->pre_selected_value)) {$select = "selected";}
            $html .= '<option value="' . esc_attr($selectable_value->value) . '" ' . esc_attr($select) . '>' . esc_html($selectable_value->name) . '</option>';
        }
        $html .= "</select>";
        return '<label>' . $html . '</label>';
    }

    private function create_multiselect()
    {

        $html = '<select multiple="multiple" ' . esc_attr($this->is_disabled($this->field)) . '  id="ff_' . esc_attr($this->prefix . $this->field->field_slug) . '"  name="' . esc_attr($this->prefix . $this->field->field_slug) . '[]">';
        foreach ($this->field->selectable_values as $selectable_value) {
            $select = "";
            if (in_array($selectable_value->value, $this->field->pre_selected_value) || count($this->field->pre_selected_value) === 0) {$select = "selected";}
            $html .= '<option value="' . esc_attr($selectable_value->value) . '" ' . esc_attr($select) . '>' . esc_html($selectable_value->name) . '</option>';
        }
        $html .= "</select>";
        return '<label>' . $html . '</label>';
    }

    private function create_radio()
    {
        $html = "";
        foreach ($this->field->selectable_values as $selectable_value) {
            $select = "";
            if ($selectable_value->value == esc_attr($this->field->pre_selected_value)) {$select = "checked";}
            $html .= '<input ' . esc_attr($this->is_disabled($this->field)) . ' id="ff_' . esc_attr($this->prefix . $this->field->field_slug) . '"  type="radio" name="' . esc_attr($this->prefix . $this->field->field_slug) . '" value="' . esc_attr($selectable_value->value) . '" ' . esc_attr($select) . ' > ' . esc_html($selectable_value->name) . ' ';
        }
        return '<label>' . $html . '</label>';
    }

    private function convert_to_string($input)
    {
        if (!is_string($input)) {
            return json_encode($input);
        }
        return $input;
    }

    private function is_disabled($field)
    {
        if ($field->disabled == true) {
            return "disabled";
        }
        return "";

    }

}
