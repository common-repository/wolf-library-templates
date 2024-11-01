<?php
defined('ABSPATH') or die;

class Wolf_Demo_Importer_Map {
	protected static $instance;
	protected $option_name		= '';
	protected $map				= array();

	public static function instance() {
		if (!self::$instance) {
			self::$instance	= new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$theme				= apply_filters('wolf_demo_importer_get_theme_name', get_template());
		$this->option_name	= $theme . '_demo_id_map';
		$this->map			= get_option($this->option_name, array());
	}

	public function save($type, $old_id, $new_id, $new = true) {
	    if (isset($this->map[$type])) {
            if (!is_array($this->map[$type])) {
                $this->map[$type]	= array();
            }
        }

		if ($new === null) {
			$this->map[$type][$old_id]	= $new_id;
		} else {
			$this->map[$type][$old_id]	= array($new_id, (int) $new);
		}

		update_option($this->option_name, $this->map);
	}

	public function get($type) {
		return isset($this->map[$type]) ? $this->map[$type] : array();
	}

	public function remove() {
		delete_option($this->option_name);
	}
}
