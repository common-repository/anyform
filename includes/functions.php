<?php

namespace Anyform;
/*
 * Add my new menu to the Admin Control Panel
 */

require_once(plugin_dir_path(__FILE__)  . 'class-data-service.php');
require_once(plugin_dir_path(__FILE__)  . '../admin/form-fields.php');
require_once(plugin_dir_path(__FILE__)  . '../admin/contacts-list.php');
require_once(plugin_dir_path(__FILE__)  . '../admin/contacts-edit.php');
require_once(plugin_dir_path(__FILE__)  . '../admin/common-ui.php');

class MF_Forms_Plugin
{
	static $instance;

	// customer WP_List_Table object
	public $customers_obj;

	public function __construct()
	{
		add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
		add_action('admin_menu', [$this, 'plugin_menu']);
	}

	public static function set_screen($status, $option, $value)
	{
		return $value;
	}

	public function plugin_menu()
	{
		$new_count = get_option('anyform_count', 0);
		$menu_title = __('Contacts List', 'af');
		if ($new_count > 0)
			$menu_title .= sprintf(' <span class="awaiting-mod">%d</span>', $new_count);

		$hook = add_menu_page(
			'Contacts',
			$menu_title, // menu title
			'manage_options',
			'mf_contacts_list',
			[$this, 'plugin_settings_page'],
			'dashicons-book',
			26
		);

		add_action("load-$hook", [$this, 'screen_option']);

		add_submenu_page(
			'mf_contacts_list',
			'Contact',
			__('Contact Details', 'af'),
			'manage_options',
			'af_contacts_edit',
			function () {
				Admin\Contacts_Edit_UI::render();
			}
		);

		add_submenu_page(
			'mf_contacts_list',
			'Form Configuration',
			__('Form Configuration', 'af'),
			'manage_options',
			'af_form_config',
			function () {
				Admin\Form_Config_UI::render();
			}
		);
	}

	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page()
	{
?>
		<div class="wrap">
			<h2><?= __('All Contacts', 'af') ?></h2>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->customers_obj->prepare_items();
								$this->customers_obj->display();
								?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option()
	{
		$option = 'per_page';
		$args   = [
			'label'   => __('Contacts', 'af'),
			'default' => 20,
			'option'  => 'contacts_per_page'
		];

		add_screen_option($option, $args);

		$this->customers_obj = new Admin\Customers_List();
	}

	/** Singleton instance */
	public static function get_instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
