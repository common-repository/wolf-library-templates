<?php
/**
 * @copyright Copyright (c) 2021 WolfCoding (https://wolfcoding.com). All rights reserved.
 */
if (!defined('ABSPATH')) {
    return;
}

class Wolf_Demo_Importer_Plugins {
    function install_plugin($plugins) {
        $plugins = (array) $plugins;

        /*check for security*/
        if (! current_user_can('install_plugins')) {
            return [
                'code'  => 2,
                'msg'   => esc_html__('Sorry, you are not allowed to install plugins on this site.', 'wolf-library-templates')
            ];
        }

        if (!empty($plugins)) {
            foreach ($plugins as $plugin) {
                $plugin         = (array) $plugin;
                $slug           = sanitize_key(wp_unslash($plugin['slug']));
                $main_file      = isset($plugin['main_file']) ? $plugin['main_file'] : $slug . '.php';
                $name           = $slug . '/' . $main_file;

                if (is_plugin_active_for_network($name) || is_plugin_active($name)) {
                    return [
                        'code'  => 1
                    ];
                }

                $status = array(
                    'install' => 'plugin',
                    'slug'    => $slug,
                );

                include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

                // Looks like a plugin is installed, but not active.
                if (file_exists(WP_PLUGIN_DIR . '/' . $slug)) {
                    $plugin_data          = get_plugin_data(WP_PLUGIN_DIR . '/' . $name);
                    $status['plugin']     = $name;
                    $status['pluginName'] = $plugin_data['Name'];

                    if (current_user_can('activate_plugin', $name) && is_plugin_inactive($name)) {
                        $result = activate_plugin($name);

                        if (is_wp_error($result)) {
                            return [
                                'code'  => 2,
                                'msg'   => $result->get_error_message()
                            ];
                        }

                        return [
                            'code'  => 1
                        ];
                    }
                }

                $api = plugins_api(
                    'plugin_information',
                    array(
                        'slug'   => sanitize_key(wp_unslash($slug)),
                        'fields' => array(
                            'sections' => false,
                        ),
                    )
                );

                if (is_wp_error($api)) {
                    return [
                        'code'  => 2,
                        'msg'   => $api->get_error_message()
                    ];
                }

                $status['pluginName'] = $api->name;

                $skin     = new WP_Ajax_Upgrader_Skin();
                $upgrader = new Plugin_Upgrader($skin);
                $result   = $upgrader->install($api->download_link);

                if (defined('WP_DEBUG') && WP_DEBUG) {
                    $status['debug'] = $skin->get_upgrade_messages();
                }

                if (is_wp_error($result)) {
                    return [
                        'code'  => 2,
                        'msg'   => $result->get_error_message()
                    ];
                } elseif (is_wp_error($skin->result)) {
                    return [
                        'code'  => 2,
                        'msg'   => $skin->result->get_error_message()
                    ];
                } elseif ($skin->get_errors()->get_error_code()) {
                    return [
                        'code'  => 2,
                        'msg'   => $skin->get_error_messages()
                    ];
                } elseif (is_null($result)) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    WP_Filesystem();
                    global $wp_filesystem;

                    $status['errorCode']    = 'unable_to_connect_to_filesystem';
                    $status['errorMessage'] = __('Unable to connect to the filesystem. Please confirm your credentials.', 'wolf-library-templates');

                    // Pass through the error from WP_Filesystem if one was raised.
                    if ($wp_filesystem instanceof WP_Filesystem_Base && is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
                        $status['errorMessage'] = esc_html($wp_filesystem->errors->get_error_message());
                    }

                    return [
                        'code'  => 2,
                        'msg'   => $status['errorMessage']
                    ];
                }

                $install_status = install_plugin_install_status($api);

                if (current_user_can('activate_plugin', $install_status['file']) && is_plugin_inactive($install_status['file'])) {
                    $result = activate_plugin($install_status['file']);

                    if (is_wp_error($result)) {
                        return [
                            'code'  => 2,
                            'msg'   => $result->get_error_message()
                        ];
                    }
                }

                return [
                    'code'  => 1,
                ];
            }
        }
    }
}