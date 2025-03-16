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
 * Update URI:        https://github.com/sagorranait/quizify/releases
 *
 * Text Domain: roleguard
 * Domain Path: /languages
 *
 */

 final class Roleguard {
    static function version() {return '1.0.0';}
    static function author_name() {return 'Sagor Rana';}
    static function min_php_version() {return '7.0';}
    static function plugin_file() {return __FILE__;}
    static function plugin_url() {return trailingslashit(plugin_dir_url(__FILE__));}
    static function plugin_dir() {return trailingslashit(plugin_dir_path(__FILE__));}

    public function __construct() {
		add_action( 'init', array( $this, 'i18n' ) );	
		add_action( 'plugins_loaded', array( $this, 'run' ));
	}

    public function i18n(){load_plugin_textdomain('roleguard', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');}
    public static function install_activation_hook(){flush_rewrite_rules();}

    public function run() {
       echo "Sagor Rana New plugin";
    }
 }

 new Roleguard();

 register_activation_hook( __FILE__, 'Roleguard::install_activation_hook' );