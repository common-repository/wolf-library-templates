<?php
defined('ABSPATH') or die;

class Wolf_Demo_Importer_Settings extends Wolf_Demo_Importer_Base {
	protected $history			= array();
	public $option_name			= '';
	public $theme_options_name	= '';

	public function __construct($data = null) {
		parent::__construct($data);

		$theme						= apply_filters('wolf_demo_importer_get_theme_name', get_template());
		$this->option_name			= $theme . '_demo_history';
		$this->theme_options_name	= '';
		$this->history				= get_option($this->option_name);
	}


	/**
	 * Save current settings.
	 */
	public function save() {
		if (isset($this->history['date'])) {
			return;
		}

		$sidebars_widgets	= get_option('sidebars_widgets');
		$current_settings	= array(
			'date'					=> time(),
			'page_on_front'			=> get_option('page_on_front'),
			'show_on_front'			=> get_option('show_on_front'),
			'nav_menu_locations'	=> get_theme_mod('nav_menu_locations'),
		);

		update_option($this->option_name, $current_settings);
	}

	/**
	 * Restore settings.
	 */
	public function restore() {
		//update_option($this->theme_options_name, $this->history['theme_options']);
		update_option('page_on_front', $this->history['page_on_front']);
    	update_option('show_on_front', $this->history['show_on_front']);
    	set_theme_mod('nav_menu_locations', $this->history['nav_menu_locations']);

    	do_action('wolf_demo_importer_restore_settings', $this->history);

    	delete_option($this->option_name);
	}

	/**
	 * Add new settings.
	 */
	public function add() {
		// add wp options
		if (isset(self::$data->show_on_front)) {
			update_option('show_on_front', self::$data->show_on_front);
		}

		do_action('wolf_demo_importer_add_settings', self::$data);
	}

	/**
	 * Remap settings.
	 */
	public function remap() {
		$map		= Wolf_Demo_Importer_Map::instance();
		$term_ids	= $map->get('terms');
		$post_ids	= $map->get('posts');

		// frontpage
		if (isset(self::$data->show_on_front) && self::$data->show_on_front == 'page') {
			if (isset(self::$data->page_on_front) && isset($post_ids[self::$data->page_on_front])) {
				update_option('page_on_front', $post_ids[self::$data->page_on_front][0]);
			}
		}

		// menu locations
		if (isset(self::$data->nav_menu_locations) && is_array(self::$data->nav_menu_locations)) {
			$locations	= array();

			if (count(self::$data->nav_menu_locations > 0)) {
                foreach (self::$data->nav_menu_locations as $location => $menu) {
                    if (isset($term_ids[$menu])) {
                        $locations[$location] = $term_ids[$menu][0];
                    }
                }
            }

			$locations	= apply_filters('wolf_demo_importer_remap_nav_menu_locations', $locations);

			set_theme_mod('nav_menu_locations', $locations);
		}

		do_action('wolf_demo_importer_remap_settings', self::$data);
	}
}
