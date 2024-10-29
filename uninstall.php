<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

require_once plugin_dir_path(__FILE__) . 'includes/install.php';

Anyform\Data\Install_Service::remove_database();