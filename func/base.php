<?php
/**
 * @copyright Copyright (c) 2021 WolfCoding (https://wolfcoding.com). All rights reserved.
 */
if (!defined('ABSPATH')) {
    return;
}

/**
 * Get config from library
 */
if (! function_exists('wolf_get_option')) {
    function wolf_get_option($option_name = '', $default = '', $name = WOLF_LIBRARY_TEMPLATES_OPTIONS) {
        $options = get_option($name);

        if (! empty($option_name) && ! empty($options[ $option_name ])) {
            return $options[ $option_name ];
        } else {
            return (! empty($default)) ? $default : null;
        }

    }
}

/**
 * Get array value.
 */
if (! function_exists('wolf_get_value_in_array')) {
    function wolf_get_value_in_array($array, $key, $default = false) {
        return isset($array[ $key ]) ? $array[ $key ] : $default;
    }
}