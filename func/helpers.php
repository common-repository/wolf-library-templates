<?php
/**
 * @copyright Copyright (c) 2021 WolfCoding (https://wolfcoding.com). All rights reserved.
 */
if (!defined('ABSPATH')) {
    return;
}

/**
 * Handle ajax demo importer.
 */
if (!function_exists('wolf_demo_importer_action')) {
    function wolf_demo_importer_action() {

        if (!isset($_POST['demo_id']) || !isset($_POST['wolf_demo_importer_action']) || !current_user_can('switch_themes')) {
            die;
        }

        set_time_limit(0);

        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';

            WP_Filesystem();
        }

        $action		= sanitize_text_field($_POST['wolf_demo_importer_action']);
        $demo_id	= sanitize_text_field($_POST['demo_id']);
        $pni		= isset($_POST['pni']) ? sanitize_text_field($_POST['pni']) : 0;

        if ($action == 'uninstall') {
            $wolf_demo_import_seting = new Wolf_Demo_Importer_Settings();
            $wolf_demo_import_seting->restore();

            $wolf_demo_import_post	= new Wolf_Demo_Importer_Post();
            $wolf_demo_import_post->remove();

            $wolf_demo_import_term	= new Wolf_Demo_Importer_Term();
            $wolf_demo_import_term->remove();

            $wolf_demo_import_user	= new Wolf_Demo_Importer_User();
            $wolf_demo_import_user->remove();

            do_action('wolf_demo_importer_finish_uninstall');

            $x = Wolf_Demo_Importer_Map::instance();
            $x->remove();
            Wolf_Demo_Importer_State::update_state('');
        } else {
            // load data
            $data	= wolf_demo_importer_load_data_file($demo_id);

            if (!$data) {
                echo 0;
                die;
            }

            switch ($action) {
                case 'install':
                    Wolf_Demo_Importer_State::update_state($demo_id);

                    $settings	= new Wolf_Demo_Importer_Settings($data);
                    $settings->save();
                    $settings->add();

                    $response	= array(
                        'next_action'	=> 'term',
                        'progress'		=> 10
                    );

                    break;
                case 'term':
                    $term	= new Wolf_Demo_Importer_Term($data);
                    $term->add();

                    $user	= new Wolf_Demo_Importer_User($data);
                    $user->add();

                    $response	= array(
                        'next_action'	=> 'post',
                        'progress'		=> 25
                    );

                    break;
                case 'post':
                    $time_out	= 5;	// 5s
                    $start_time	= time();
                    $index		= $pni;
                    $post		= new Wolf_Demo_Importer_Post($data);

                    do {
                        $index		= $post->add_by_index($index);
                        $end_time	= time();
                    } while ($index && $end_time - $start_time < $time_out);

                    if ($index) {
                        $response	= array(
                            'next_action'	=> 'post',
                            'pni'			=> $index,
                            'progress'		=> 25 + intval(40 * $index / count($data->posts)),
                        );
                    } else {
                        $response	= array(
                            'next_action'	=> 'remap',
                            'progress'		=> 65
                        );
                    }

                    break;
                case 'remap':
                    $x = new Wolf_Demo_Importer_Settings($data);
                    $x->remap();

                    $y = new Wolf_Demo_Importer_Term($data);
                    $y->remap();

                    $z = new Wolf_Demo_Importer_Post($data);
                    $z->remap();

                    $t = new Wolf_Demo_Importer_User($data);
                    $t->remap();

                    $response	= array(
                        'next_action'	=> 'finish',
                        'progress'		=> 90,
                    );

                    break;
                case 'finish':
                    do_action('wolf_demo_importer_finish_install', $demo_id, $data);
                    echo 1;
                    die;
            }

            if (isset($response)) {
                echo json_encode($response);
                die;
            } else {
                echo 0;
                die;
            }
        }
    }

    add_action('wp_ajax_wolf_demo_importer_action', 'wolf_demo_importer_action');
    add_action('wp_ajax_nopriv_wolf_demo_importer_action', 'wolf_demo_importer_action');
}

/**
 * Load demo data file.
 */
if (!function_exists('wolf_demo_importer_load_data_file')) {
    function wolf_demo_importer_load_data_file($id) {
        $file	= apply_filters('wolf_demo_importer_get_data_file', '', $id);

        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';

            WP_Filesystem();
        }

        if ($file) {
            return json_decode($wp_filesystem->get_contents($file));
        }

        return null;
    }
}
