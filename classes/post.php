<?php
defined('ABSPATH') or die;

class Wolf_Demo_Importer_Post extends Wolf_Demo_Importer_Base {
	protected $remote_url	= '';

	public function __construct($data = null) {
		parent::__construct($data);

		if (isset(self::$data->site_url)) {
			$this->remote_url	= self::$data->site_url;
		}
	}

	public function add() {
		if (isset(self::$data->posts) && is_array(self::$data->posts)) {
			foreach (self::$data->posts as $post) {
				$this->add_post($post);
			}
		}
	}

	public function add_by_index($index = 0) {
		if (isset(self::$data->posts[$index])) {
			$this->add_post(self::$data->posts[$index]);

			if ($index == count(self::$data->posts) - 1) {
				return false;
			} else {
				return $index + 1;
			}
		}

		return false;
	}

	public function add_post($post) {
		$post_type	= $post->type;
		$map		= Wolf_Demo_Importer_Map::instance();

		if (in_array($post_type, apply_filters('wolf_demo_importer_get_post_type_unique', array('post', 'page', 'product'))) && ($new_id = post_exists($post->title, $post->content))) {
			$new	= false;
		} else {
			$new	= true;

			$new_post	= array(
				'post_title'		=> $post->title,
				'post_type'			=> $post_type,
				'post_status'		=> $post->status,
				'post_excerpt'		=> $post->excerpt,
				'post_content'		=> $post->content,
				'menu_order'		=> $post->menu_order,
				'comment_status'	=> $post->comment_status,
			);

			if ($post_type == 'attachment') {
				require_once(ABSPATH . 'wp-admin/includes/media.php');
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				require_once(ABSPATH . 'wp-admin/includes/image.php');

				// Set variables for storage, fix file filename for query strings.
				$file 			= array();
				$file['name']	= basename($post->title);

				// Download file to temp location.
				$meta		= $post->meta;
				$remove_url	= $this->remote_url . '/wp-content/uploads/' . $meta->_wp_attached_file;

				preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $remove_url, $matches);
				$file['name']		= basename($matches[0]);
				$file['tmp_name']	= download_url($remove_url);

				// If error storing temporarily, return the error.
				if (is_wp_error($file['tmp_name'])) {
					return;
				}

				// Do the validation and storage stuff.
				$new_id		= media_handle_sideload($file, '', $post->content);

				$new_path	= get_post_meta($new_id, '_wp_attached_file');

				$map->save('media', $meta->_wp_attached_file, $new_path[0], null);
			} else {
				if ($post_type == 'product_variation') {
					$ids	= $map->get('posts');

					if (isset($ids[$post->parent])) {
						$new_post['post_parent']	= $ids[$post->parent];
					}
				}

				$new_id	= wp_insert_post($new_post);

				if (isset($post->meta)) {
					foreach ($post->meta as $key => $value) {
						if ($key == '_product_attributes' && $value) {
							null;
						}

						$unserialized	= @unserialize($value);

						add_post_meta($new_id, $key, $unserialized ? $unserialized : $value);
					}
				}
			}

			// comments
			if (isset($post->comments)) {
				foreach ($post->comments as $comment) {
					$comment_id	= wp_insert_comment(array(
						'comment_post_ID'		=> $new_id,
						'comment_author'		=> $comment->author,
						'comment_author_email'	=> $comment->author_email,
						'comment_content'		=> $comment->comment_content,
						'comment_type'			=> '',
						'comment_parent'		=> $comment->parent,
						'comment_date'			=> $comment->date,
						'comment_approved'		=> 1,
					));

					if ($comment_id && isset($comment->meta)) {
						foreach ($comment->meta as $key => $value) {
							$unserialized	= unserialize($value);

							add_comment_meta($comment_id, $key, $unserialized ? $unserialized : $value);
						}
					}
				}
			}

			do_action('wolf_demo_importer_add_post', $post, $new_id);
		}

		$map->save('posts', $post->id, $new_id, $new);
	}

	public function remove() {
		$ids	= Wolf_Demo_Importer_Map::instance()->get('posts');

		foreach ($ids as $id) {
			if ($id[1]) {
				wp_delete_post($id[0], true);

				do_action('wolf_demo_importer_delete_post', $id[0]);
			}
		}
	}

	public function remap() {
		if (isset(self::$data->posts) && is_array(self::$data->posts)) {
			global $wpdb;

			$map		= Wolf_Demo_Importer_Map::instance();
			$term_ids	= $map->get('terms');
			$post_ids	= $map->get('posts');

			foreach (self::$data->posts as $post) {
				if (isset($post_ids[$post->id]) && $post_ids[$post->id][0] && $post_ids[$post->id][1]) {
					$new_post	= $post_ids[$post->id][0];
					$post_data	= array(
						'ID'		=> $new_post,
					);

					// remap terms
                    if (isset($post->terms)) {
                        if (is_array($post->terms)) {
                            if (count($post->terms)) {
                                foreach ($post->terms as $term) {
                                    if (isset($term_ids[$term][0])) {
                                        $t = get_term($term_ids[$term][0]);

                                        wp_set_object_terms($new_post, $t->term_id, $t->taxonomy, true);
                                    }
                                }
                            }
                        }
                    }

					// remap post parent
					if (isset($post_ids[$post->parent])) {
						$post_data['post_parent']	= $post_ids[$post->parent][0];
					}

					// remap thumbnail
					if (isset($post->meta->_thumbnail_id) && isset($post_ids[$post->meta->_thumbnail_id])) {
						update_post_meta($new_post, '_thumbnail_id', $post_ids[$post->meta->_thumbnail_id][0]);
					}

					// remap product image gallery
					if (isset($post->meta->_product_image_gallery)) {
						$gallery	= array();
						$images		= explode(',', $post->meta->_product_image_gallery);

						foreach ($images as $image) {
							if (isset($post_ids[$image])) {
								$gallery[]	= $post_ids[$image][0];
							}
						}

						update_post_meta($new_post, '_product_image_gallery', implode(',', $gallery));
					}

					// remap menu
					if ($post->type == 'nav_menu_item') {
						if (isset($post->meta->_menu_item_type)) {
							if ($post->meta->_menu_item_type == 'post_type') {
								$post_id	= $post->meta->_menu_item_object_id;

								if (isset($post_ids[$post_id])) {
									update_post_meta($new_post, '_menu_item_object_id', $post_ids[$post_id][0]);
								}
							} else if ($post->meta->_menu_item_type == 'taxonomy') {
								$tax_id	= $post->meta->_menu_item_object_id;

								if (isset($term_ids[$tax_id])) {
									update_post_meta($new_post, '_menu_item_object_id', $term_ids[$tax_id][0]);
								}
							}
						}

						if (isset($post->meta->_menu_item_menu_item_parent)) {
							$post_id	= $post->meta->_menu_item_menu_item_parent;

							if (isset($post_ids[$post_id])) {
								update_post_meta($new_post, '_menu_item_menu_item_parent', $post_ids[$post_id][0]);
							}
						}
					}

					// remap content
                    if (isset($post->exerpt)) {
                        if ($exerpt = $this->process_content($post->exerpt)) {
                            $post_data['post_exerpt'] = $exerpt;
                        }
                    }

                    if (isset($post->content)) {
                        if ($content = $this->process_content($post->content)) {
                            $post_data['post_content'] = $content;
                        }
                    }

					if (count($post_data) > 1) {
						wp_update_post($post_data);
					}

					do_action('wolf_demo_importer_remap_post', $post);
				}
			}
		}
	}

	protected function process_content($content) {
		$changed		= false;
		$map			= Wolf_Demo_Importer_Map::instance();
		$term_ids		= $map->get('terms');
		$post_ids		= $map->get('posts');
		$media_paths	= $map->get('media');
		$user_ids		= $map->get('users');

		$regex		= '/##term##([0-9,]+)##/';

		if (preg_match_all($regex, $content, $matches)) {
			$changed	= true;

			for ($i = 0, $n = count($matches[0]); $i < $n; $i++) {
				$terms		= explode(',', $matches[1][$i]);
				$new_terms	= array();

				foreach ($terms as $term) {
					if (isset($term_ids[$term])) {
						$new_terms[]	= $term_ids[$term][0];
					}
				}

				if (count($new_terms)) {
					$content	= str_replace($matches[0][$i], implode(',', $new_terms), $content);
				}
			}
		}

		$regex		= '/##post##([0-9,]+)##/';

		if (preg_match_all($regex, $content, $matches)) {
			$changed	= true;

			for ($i = 0, $n = count($matches[0]); $i < $n; $i++) {
				$posts		= explode(',', $matches[1][$i]);
				$new_posts	= array();

				foreach ($posts as $post) {
					if (isset($post_ids[$post])) {
						$new_posts[]	= $post_ids[$post][0];
					}
				}

				if (count($new_posts)) {
					$content	= str_replace($matches[0][$i], implode(',', $new_posts), $content);
				}
			}
		}

		$regex	= '`(http:|https:)*//.*?\.(jpg|jpeg|png|gif|mp3|mp4|ogv|webm)`';

		if (preg_match_all($regex, $content, $matches)) {
			$site_url	= is_multisite() ? network_home_url() : home_url('/');

			for ($i = 0, $n = count($matches[0]); $i < $n; $i++) {
				$media_url	= $matches[0][$i];

				if (strpos($media_url, $this->remote_url) === 0) {
					if (strpos($media_url, 'wp-content/uploads/') !== false) {
						$path	= preg_replace('`(.*)wp-content/uploads/(.*?)(-\d+x\d+)?\.(jpg|jpeg|png|gif|mp3|mp4|ogv|webm)`', '$2.$4', $media_url);

						if (isset($media_paths[$path])) {
							$changed	= true;
							$content	= str_replace($matches[0][$i], $site_url . 'wp-content/uploads/' . $media_paths[$path], $content);
						}
					}
				}
			}
		}

		$regex		= '/##user##([0-9,]+)##/';

		if (preg_match_all($regex, $content, $matches)) {
			$changed	= true;

			for ($i = 0, $n = count($matches[0]); $i < $n; $i++) {
				$users		= explode(',', $matches[1][$i]);
				$new_users	= array();

				foreach ($users as $user) {
					if (isset($user_ids[$user])) {
						$new_users[]	= $user_ids[$user][0];
					}
				}

				if (count($new_users)) {
					$content	= str_replace($matches[0][$i], implode(',', $new_users), $content);
				}
			}
		}

		if ($changed) {
			return $content;
		}

		return false;
	}
}
