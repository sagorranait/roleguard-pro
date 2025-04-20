<?php
defined('ABSPATH') || exit;

/**
 * Plugin Name:       RoleGuard Pro
 * Description:       Create limited-access admin roles to improve WordPress security in multi-user environments.
 * Plugin URI:        https://github.com/sagorranait/roleguard-pro
 * Author:            Sagor Rana
 * Author URI:        https://github.com/sagorranait/
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       roleguard
 * Domain Path:       /languages
 */

final class RoleGuard_Pro {
	const ROLE = 'admin';
	const VERSION = '1.0.0';

	public function __construct() {
		add_action('init', [$this, 'load_textdomain']);
		add_action('plugins_loaded', [$this, 'init_plugin']);
	}

	public function load_textdomain() {
		load_plugin_textdomain('roleguard', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}

	public static function activate() {
		$admin = get_role('administrator');
		if (!$admin) return;

		$caps = $admin->capabilities;
		$caps['gravityforms_edit_forms'] = true;
		$caps['gravityforms_view_entries'] = true;
		unset(
            $caps['activate_plugins'], $caps['install_plugins'], $caps['edit_plugins'],
			$caps['delete_plugins'], $caps['update_plugins'], $caps['create_users'],
			$caps['edit_users'], $caps['delete_users'], $caps['list_users'],
			$caps['promote_users'], $caps['remove_users'], $caps['import'],
			$caps['export'], $caps['acf/manage_options']
		);
		add_role(self::ROLE, 'Admin', $caps);
		flush_rewrite_rules();
	}

	public static function deactivate() {
		remove_role(self::ROLE);
		flush_rewrite_rules();
	}

	public function init_plugin() {
		if (!$this->is_custom_admin()) return;

		add_action('admin_head', [$this, 'inject_admin_css_js']);
		add_action('admin_menu', [$this, 'hide_admin_menus'], 999);
		add_action('admin_init', [$this, 'restrict_access']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
	}

	private function is_custom_admin() {
		$user = wp_get_current_user();
		return in_array(self::ROLE, (array) $user->roles, true);
	}

	public function inject_admin_css_js() {
		echo '<style>
			#menu-plugins, .wp-submenu li a[href*="plugins.php"],
			a[href*="acf-tools"], a[href*="gf_export"],
			a.elementor-template-library-export-template { display: none !important; }
		</style>';
		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				document.querySelectorAll(".elementor-template-library-export-template, .export-template, #elementor-settings-tab-import-export-kit, #tab-import-export-kit, .elementor-template-library-template-export").forEach(el => el.remove());
			});
		</script>';
	}

	public function hide_admin_menus() {
		remove_menu_page('plugins.php');
		remove_menu_page('users.php');
		remove_submenu_page('edit.php?post_type=acf-field-group', 'acf-tools');
		remove_submenu_page('gf_edit_forms', 'gf_export');
	}

	public function restrict_access() {
		$restricted_pages = [
			'plugins.php', 'plugin-install.php', 'plugin-editor.php',
			'edit.php?post_type=acf-field-group&page=acf-tools', 'admin.php?page=gf_export'
		];
		if (in_array($GLOBALS['pagenow'], $restricted_pages, true) || isset($_GET['page']) && in_array($_GET['page'], ['acf-tools', 'gf_export'], true)) {
			wp_safe_redirect(admin_url());
			exit;
		}
	}

	public function enqueue_assets() {
		wp_enqueue_script('roleguard-admin-js', plugin_dir_url(__FILE__) . 'assets/js/elementor-scripts.js', ['jquery'], self::VERSION, true);
		wp_enqueue_style('roleguard-admin-css', plugin_dir_url(__FILE__) . 'assets/css/elementor-hide.css', [], self::VERSION);
	}
}

new RoleGuard_Pro();

register_activation_hook(__FILE__, ['RoleGuard_Pro', 'activate']);
register_deactivation_hook(__FILE__, ['RoleGuard_Pro', 'deactivate']);