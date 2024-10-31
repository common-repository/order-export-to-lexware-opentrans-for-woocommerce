<?php

class input_validation_nscwto
{
    private $admin_error_obj;

    public function __construct()
    {
        $this->admin_error_obj = new admin_error_nscwto;
    }

    public function validate_field_custom_save($extra_validation_value, $input)
    {
        $return = $this->sanitize_input($input);
        switch ($extra_validation_value) {
            case "check_valid_json_string":
                $return = $this->check_valid_json_string($return);
                break;
            case "integer":
                $return = $this->integer($return);
                break;
            case "integer_number_list":
                $return = $this->integer_number_list($return);
                break;
            case "validateDate":
                $return = $this->validateDate($return);
                break;
        }
        $return = apply_filters('filter_input_validation_nscwto', $return, $extra_validation_value);
        return $return;
    }

    private function sanitize_input($input)
    {
        if (is_array($input)) {
            for ($i = 0; $i < count($input); $i++) {
                $input[$i] = stripslashes($input[$i]);
                $input[$i] = sanitize_text_field($input[$i]);
            }
            return $input;
        }

        $cleandValue = stripslashes($input);
        return sanitize_text_field($cleandValue);
    }

    private function integer_number_list($input)
    {
        $input = trim($input, ",");
        $array = explode(",", $input);
        $trimmed_array = array();
        foreach ($array as $int) {
            $trimmed_array[] = trim($int);
            if ($this->integer($int) === null) {
                $this->admin_error_obj->set_admin_error(__("Please provide a comma separated list of integers"));
                return null;
            }
        }
        return implode(",", $trimmed_array);
    }

    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        if ($d && $d->format($format) === $date) {
            return $date;
        }
        $this->admin_error_obj->set_admin_error(__("The date you entered has not the expected format: Y-m-d. Your input: ") . $input);
        return null;
    }

    private function integer($input)
    {
        $input = trim($input);
        $valid = preg_match("/^[0-9]*$/", $input);
        if (empty($valid) && $input != "") {
            $this->admin_error_obj->set_admin_error(__("Number could not be saved. Please provide an integer. Your input: ") . $input);
            return null;
        }
        return $input;
    }

    private function check_valid_json_string($json_string)
    {
        if ($json_string == "1") {
            return null;
        }

        $php_version_good = $this->php_version_good();
        switch ($php_version_good) {
            case true:
                $tested_json_string = json_encode(json_decode($json_string), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                break;
            default:
                $tested_json_string = json_encode(json_decode($json_string));
                break;
        }

        if (empty($tested_json_string) || $tested_json_string == "null") {
            $this->admin_error_obj->set_admin_error("Please provide a valid json string. Data was not saved.");
            return null;
        } else {
            return $tested_json_string;
        }
    }

    public function php_version_good($minVersion = '5.4.0')
    {
        if (version_compare(phpversion(), $minVersion, '>=')) {
            return true;
        } else {
            return false;
        }
    }

    public function return_errors_obj()
    {
        return $this->admin_error_obj;
    }

}
