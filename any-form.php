<?php
/*
Plugin Name:         AnyForm
Description:         Very simple and customisable contact form with database integration and control panel
Author:              Yigal Hasin
Version:             1.0.1
Requires at least:   5.4
Requires PHP:        7.2
License:             GPL v2 or later
License URI:         https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:		 af
Domain Path: 		/languages/
*/

namespace Anyform;

require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/install.php';
require_once plugin_dir_path(__FILE__) . 'public/form-ui.php';

register_activation_hook(__FILE__, function () {
	Data\Install_Service::install('0.1');
});

add_shortcode('any-form', function ($attr) {
	return Front\Form_UI::render($attr);
});

add_action('plugins_loaded', function () {
	MF_Forms_Plugin::get_instance();
});
