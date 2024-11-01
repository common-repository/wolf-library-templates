<?php
/**
 * @copyright Copyright (c) 2021 WolfCoding (https://wolfcoding.com). All rights reserved.
 */
if (!defined('ABSPATH')) {
    return;
}

if (!function_exists('wolf_demo_importer_get_data_file')) {
    function wolf_demo_importer_get_data_file($file, $id) {
        $file	= esc_url(WOLF_LIBRARY_TEMPLATES_DIR_URL . '/templates/' . $id . '/content.json');

        return $file;
    }

    add_filter('wolf_demo_importer_get_data_file', 'wolf_demo_importer_get_data_file', 10, 2);
}

if (!function_exists('wolf_library_lists')) {
    function wolf_library_lists() {
        $lists = [
            'education' => [
                'title'             => esc_html__('Education', 'wolf-library-templates'),
                'is_pro'            => false,
                'type'              => 'elementor',
                'keywords'          => ['education'],
                'categories'        => ['education'],
                'screenshot_url'    => WOLF_LIBRARY_TEMPLATES_DIR_URL . '/templates/education/screenshot.png',
                'demo_url'          => 'http://theme.wolfcoding.com/education/',
                'plugins'           => [
                    [
                        'name'      => esc_html__('Elementor'),
                        'slug'      => 'elementor'
                    ]
                ]
            ],
        ];

        return $lists;
    }

    add_filter('wolf_lt_demo_lists','wolf_library_lists');
}
