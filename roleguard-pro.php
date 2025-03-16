<?php
 defined( 'ABSPATH' ) || exit;

/**
 * Plugin Name: RoleGuard Pro
 * Description: RoleGuard Pro is a WordPress plugin that enhances security by allowing you to create custom user roles with specific capabilities. You can add an "Admin" role with limited access to plugins and sensitive features, ensuring security while delegating tasks effectively. It's ideal for multi-user environments.
 * Plugin URI: https://github.com/sagorranait/roleguard-pro
 * Author: Sagor Rana
 * Author URI: https://github.com/sagorranait/
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/sagorranait/roleguard-pro/releases
 *
 * Text Domain: roleguard
 * Domain Path: /languages
 *
 */

 final class Roleguard {

    static function version() {
        return '1.0.0';
    }

    static function author_name() {
        return 'Sagor Rana';
    }

    static function min_php_version() {
        return '7.0';
    }

    static function plugin_file() {
        return __FILE__;
    }

    static function plugin_url() {
        return trailingslashit(plugin_dir_url(__FILE__));
    }

    static function plugin_dir() {
        return trailingslashit(plugin_dir_path(__FILE__));
    }

    static function assets_url() {
		return self::plugin_url() . 'assets/';
	}

    public function __construct() {
		add_action( 'init', array( $this, 'i18n' ) );	
		add_action( 'plugins_loaded', array( $this, 'run' ));
	}

    public function i18n(){
        load_plugin_textdomain('roleguard', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
    }

    public static function install_activation_hook(){        
        $admin_capabilities = get_role('administrator')->capabilities;
        $admin_capabilities['gravityforms_edit_forms'] = true; 
        $admin_capabilities['gravityforms_view_entries'] = true; 
        $admin_capabilities['gravityforms_export_entries'] = false; 
        unset($admin_capabilities['activate_plugins'],$admin_capabilities['install_plugins'],$admin_capabilities['edit_plugins'],$admin_capabilities['delete_plugins'],$admin_capabilities['update_plugins'],$admin_capabilities['create_users'],$admin_capabilities['edit_users'],$admin_capabilities['delete_users'],$admin_capabilities['list_users'],$admin_capabilities['promote_users'],$admin_capabilities['remove_users'],$admin_capabilities['import'],$admin_capabilities['export'],$admin_capabilities['acf/manage_options']);
        add_role('admin', 'Admin', $admin_capabilities);
        flush_rewrite_rules();
    }

    public static function install_deactivation_hook(){
        remove_role('admin');
        flush_rewrite_rules();
    }

    public function restrict_admin_role_access() {
        if ($this->is_custom_admin()) {
            remove_menu_page('plugins.php');
            remove_menu_page('users.php');
            remove_submenu_page('edit.php?post_type=acf-field-group', 'acf-tools');
            remove_submenu_page('gf_edit_forms', 'gf_export');
        }
    }

    public function hide_menus_for_admin_role() {
        if ($this->is_custom_admin()) {
            remove_menu_page('plugins.php'); // Hide Plugins menu
            remove_submenu_page('edit.php?post_type=acf-field-group', 'acf-tools'); // Hide ACF Tools
            remove_submenu_page('gf_edit_forms', 'gf_export'); // Hide Gravity Forms Import/Export
        }
    }

    public function redirect_admin_role_from_restricted_pages() {
        if ($this->is_custom_admin()) {
            $restricted_pages = ['plugins.php', 'plugin-install.php', 'plugin-editor.php', 'edit.php?post_type=acf-field-group&page=acf-tools', 'admin.php?page=gf_export'];
            if (in_array($GLOBALS['pagenow'], $restricted_pages) || isset($_GET['page']) && in_array($_GET['page'], ['acf-tools', 'gf_export'])) {
                wp_redirect(admin_url());
                exit;
            }
        }
    }

    public function hide_admin_elements() {
        if ($this->is_custom_admin()) {
            echo '<style>
                #menu-plugins, .wp-submenu li a[href*="plugins.php"],
                a[href*="acf-tools"], a[href*="gf_export"],
                a.elementor-template-library-export-template {display: none !important;}
            </style>';
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    let exportBtns = document.querySelectorAll(".elementor-template-library-export-template");
                    exportBtns.forEach(btn => btn.parentNode.removeChild(btn));
                });
            </script>';
        }
    }

    public function hide_export_template_js_for_non_admins() {
        if ($this->is_custom_admin()) {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelectorAll(".export-template").forEach(function(element) {
                        element.remove();
                    });
        
                    document.querySelectorAll("#elementor-settings-tab-import-export-kit").forEach(function(element) {
                        element.remove();
                    });
        
                    document.querySelectorAll("#tab-import-export-kit").forEach(function(element) {
                        element.remove();
                    });

                    document.querySelectorAll(".elementor-template-library-template-export").forEach(function(element) {
                        element.remove();
                    });
                });
            </script>';
        }
    }

    public function admin_enqueue() {
        if ($this->is_custom_admin()) {
            wp_enqueue_style('roleguard-init-admin-css', self::assets_url() . 'css/elementor-hide.css', array(), self::version(), 'all');
        }
    }

    public function run() {
        add_action('admin_head', array($this, 'hide_admin_elements'));
        add_action('admin_init', array($this, 'restrict_admin_role_access'));
        add_action( 'wp_enqueue_scripts', array( $this, 'admin_enqueue' ) );
        add_action('admin_menu', array($this, 'hide_menus_for_admin_role'), 999);
        add_action('admin_footer', array($this, 'hide_export_template_js_for_non_admins'));
        add_action('admin_init', array($this, 'redirect_admin_role_from_restricted_pages'));
    }

    private function is_custom_admin() {
        $user = wp_get_current_user();
        return in_array('admin', (array) $user->roles);
    }
 }

 new Roleguard();

 register_activation_hook( __FILE__, 'Roleguard::install_activation_hook' );
 register_deactivation_hook( __FILE__, 'Roleguard::install_deactivation_hook' );