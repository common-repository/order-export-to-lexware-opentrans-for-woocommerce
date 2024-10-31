<?php

class Order_To_Lexware_nscwto
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Woo_Order_To_Lexware_Loader_NSCWTO    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    public function __construct()
    {
        if (defined('NSC_WTO_VERSION')) {
            $this->version = NSC_WTO_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'order-export-to-lexware-opentrans-for-woocommerce';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();

    }

    private function load_dependencies()
    {
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-admin-error-nscwto.php';
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-validation-nscwto.php';
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-plugin-configs-nscwto.php';
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-loader-nscwto.php';
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-i18n-nscwto.php';
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-download-orders-nscwto.php';
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-html-formfields-nscwto.php';
        require plugin_dir_path(dirname(__FILE__)) . 'admin/class-admin-nscwto.php';
        $this->loader = new Loader_nscwto();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new i18n_nscwto();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Admin_nscwto($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'download_order_export');
        $this->loader->add_action('plugins_loaded', $plugin_admin, 'save_settings');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Woo_Order_To_Lexware_Loader_NSCWTO    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}
