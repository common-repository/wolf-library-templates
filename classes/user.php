<?php
defined('ABSPATH') or die;

class Wolf_Demo_Importer_User extends Wolf_Demo_Importer_Base {

	public function add() {
		if (isset(self::$data->users) && is_array(self::$data->users)) {
			foreach (self::$data->users as $user) {
				$this->add_user($user);
			}
		}
	}

	public function add_user($user) {
		if ($new_id = username_exists($user->user_login)) {
			$new		= false;
		} else {
			$new				= true;

			$userdata = array(
				'user_login'		=> $user->user_login,
				'user_pass'			=> $user->user_pass,
				'user_nicename'		=> $user->user_nicename,
				'user_email'		=> $user->user_email,
				'user_url'			=> $user->user_url,
				'user_registered'	=> $user->user_registered,
				'user_status'		=> $user->user_status,
				'display_name'		=> $user->display_name,
			);

			$new_id = wp_insert_user($userdata);

			$meta	= (array) $user->meta;

			if (count($meta)) {
				foreach ($meta as $key => $value) {
					update_user_meta($new_id, $key, $value);
				}
			}

			$user_id_role = new WP_User($new_id);
			$user_id_role->set_role('vendor');

			do_action('wolf_demo_importer_add_user', $user, $new_id);
		}

		Wolf_Demo_Importer_Map::instance()->save('users', $user->id, $new_id, $new);
	}

	public function remove() {
		$ids	= Wolf_Demo_Importer_Map::instance()->get('users');

		foreach ($ids as $id) {
			if ($id[1]) {
				wp_delete_user($id[0]);

				do_action('wolf_demo_importer_delete_user', $id[0]);
			}
		}
	}

	public function remap() {
		if (isset(self::$data->users) && is_array(self::$data->users)) {
			$map		= Wolf_Demo_Importer_Map::instance();
			$term_ids	= $map->get('terms');
			$post_ids	= $map->get('posts');

			foreach (self::$data->users as $user) {
				do_action('wolf_demo_importer_remap_user', $user);
			}
		}
	}
}
