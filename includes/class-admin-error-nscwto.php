<?php

class admin_error_nscwto
{
    private $errors;

    public function __construct()
    {
        $this->errors = array();
    }

    public function display_errors()
    {
        if (!empty($this->errors)) {
            add_action('admin_notices', array($this, 'echo_admin_errors'));
        }
    }

    public function set_admin_error($error, $type = "error")
    {
        $this->errors[$type][] = $error;
    }

    public function echo_admin_errors()
    {
        $uniqueErrors = array_unique($this->errors);
        foreach ($uniqueErrors as $error_type => $type) {
            $uniqueErrorTypes = array_unique($type);
            foreach ($uniqueErrorTypes as $error_message) {
                echo '<div class="notice notice-error">
                       <p>' . __(wp_kses_post($error_message), "order-to-lexware-nscwto") . '</p>
                    </div>';
            }
        }
    }

}
