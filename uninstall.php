<?php

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

class uninstaller_nsc_wto
{
    public function uninstall()
    {
        delete_option("nscwto_plugin_settings");
    }

}

$uninstaller_nsc_wto = new uninstaller_nsc_wto;
$uninstaller_nsc_wto->uninstall();
