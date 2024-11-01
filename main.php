<?php
/*
Plugin Name: 		Wolf Library Templates
Description:		Elementor Library Templates
Version: 			1.0.0
Author: 			wolfcoding
Author URI:			https://wolfcoding.com/
*/

if (!defined('ABSPATH')) {
    return;
}

if (!class_exists('Wolf_Library_Templates')) {
    class Wolf_Library_Templates {
        public function __construct() {
            require_once 'define.php';

            $this->load_library();
            $this->load_helper();

            add_action('init', array(__CLASS__, 'load_config'), 2);
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }

        public function load_library() {
            // Load core framework
            if (!class_exists('CSF')) {
                require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/libs/codestar-framework/codestar-framework.php';
            }

            // Load template framework
            if (!class_exists('Gamajo_Template_Loader')) {
                //require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/libs/templates/class-gamajo-template-loader.php';
            }
        }

        public static function load_config() {
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/config/settings.php';
        }

        // load helper.
        public function load_helper() {
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/classes/admin.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/classes/base.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/classes/map.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/classes/settings.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/classes/post.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/classes/term.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/classes/user.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/classes/state.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/classes/plugin.php';

            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/func/base.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/func/helpers.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/func/hooks.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/func/filters.php';
            require_once WOLF_LIBRARY_TEMPLATES_DIR_PATH . '/func/library.php';
        }

        public function admin_enqueue_scripts() {
            wp_enqueue_style('wolf-library-templates', WOLF_LIBRARY_TEMPLATES_DIR_URL . '/assets/css/style.css', array());

            wp_enqueue_script('imagesloaded');
            wp_enqueue_script('sweetalert', WOLF_LIBRARY_TEMPLATES_DIR_URL . '/assets/js/vendor/sweetalert2/sweetalert2.all.min.js', array('jquery'), false, true);
            wp_enqueue_script('isotope', WOLF_LIBRARY_TEMPLATES_DIR_URL . '/assets/js/vendor/isotope/isotope.pkgd.min.js', array('jquery'), false, true);
            wp_enqueue_script('wolf-library-templates-core', WOLF_LIBRARY_TEMPLATES_DIR_URL . '/assets/js/core.js', array('jquery'), false, true);
            wp_enqueue_script('wolf-library-templates', WOLF_LIBRARY_TEMPLATES_DIR_URL . '/assets/js/script.js', array('jquery'), false, true);
            wp_localize_script('wolf-library-templates', 'adiL10n', array(
                'install_demo_confirm'		=> __(
                    "Install demo content:\n"
                    . "-----------------------------------------\n"
                    . "Are you sure? This will install demo content\n\n"
                    . "It will install the plugins required for demo and activate them\n\n"
                    . "This may add demo posts, images, slideshows and settings into your website.\n\n"
                    . "You can remove them later by clicking uninstall demo content.\n\n"
                    . "Please backup your settings to be sure that you don't lose them by accident.\n\n\n"
                ),
                'uninstall_demo_confirm'	=> __(
                    "Uninstall demo content:\n"
                    . "-----------------------------------------\n"
                    . "Are you sure? This will remove demo posts, images, slideshows and settings from your website.\n\n\n"
                ),
                'install_demo_error'		=> esc_html__('Error installing demo content!', 'wolf-library-templates'),
                'uninstall_demo_error'		=> esc_html__('Error uninstalling demo content!', 'wolf-library-templates'),
                'install_finish'            => esc_html__('Install Finish', 'wolf-library-templates')
            ));
        }
    }

    new Wolf_Library_Templates();
}