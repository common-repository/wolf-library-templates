<?php
defined('ABSPATH') or die;

class Wolf_Demo_Importer_State {

	static function update_state($demo_id) {
		$theme			= apply_filters('wolf_demo_importer_get_theme_name', get_template());
		$option_name	= $theme . '_demo_state';

		update_option($option_name, $demo_id);
	}

	static function get_installed_demo() {
		$theme			= apply_filters('wolf_demo_importer_get_theme_name', get_template());
		$option_name	= $theme . '_demo_state';
		$state			= get_option($option_name);

		return $state ? $state : false;
	}
}
