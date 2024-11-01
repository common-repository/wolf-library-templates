<?php
defined('ABSPATH') or die;

class Wolf_Demo_Importer_Term extends Wolf_Demo_Importer_Base {

	public function add() {
		if (isset(self::$data->terms) && is_array(self::$data->terms)) {
			foreach (self::$data->terms as $term) {
				$this->add_term($term);
			}
		}
	}

	public function add_term($term) {
		if ($new_id = term_exists($term->name, $term->taxonomy)) {
			$new		= false;
			$new_id		= $new_id['term_id'];
		} else {
			$new		= true;
			$new_term	= wp_insert_term($term->name, $term->taxonomy, array(
				'description'	=> $term->description
			));

			if ($new_term instanceof WP_Error) {
				return;
			}

			$new_id	= $new_term['term_id'];

			$meta	= (array) $term->meta;

			if (count($meta)) {
				foreach ($meta as $key => $value) {
					update_term_meta($new_id, $key, $value);
				}
			}

			do_action('wolf_demo_importer_add_term', $term, $new_term);
		}

		Wolf_Demo_Importer_Map::instance()->save('terms', $term->id, $new_id, $new);
	}

	public function remove() {
		$ids	= Wolf_Demo_Importer_Map::instance()->get('terms');

		foreach ($ids as $id) {
			if ($id[1]) {
				$term	= get_term($id[0]);

				wp_delete_term($term->term_id, $term->taxonomy);

				do_action('wolf_demo_importer_delete_term', $id[0]);
			}
		}
	}

	public function remap() {
		if (isset(self::$data->terms) && is_array(self::$data->terms)) {
			$map		= Wolf_Demo_Importer_Map::instance();
			$term_ids	= $map->get('terms');
			$post_ids	= $map->get('posts');

			foreach (self::$data->terms as $term) {
				if (isset($term_ids[$term->id])) {
					$new_term	= $term_ids[$term->id];

					// remap parent
					if ($new_term[1]) {
						if (isset($term_ids[$term->parent])) {
							wp_update_term($new_term[0], $term->taxonomy, array('parent' => $term_ids[$term->parent][0]));
						}

						// remap product category thumbnail
						if (isset($term->meta->thumbnail_id) && isset($post_ids[$term->meta->thumbnail_id])) {
							update_term_meta($new_term[0], 'thumbnail_id', $post_ids[$term->meta->thumbnail_id][0]);
						}
					}
				}

				do_action('wolf_demo_importer_remap_term', $term);
			}
		}
	}
}
