<?php
/**
 * @copyright Copyright (c) 2021 WolfCoding (https://wolfcoding.com). All rights reserved.
 */
if (!defined('ABSPATH')) {
    return;
}

if (!class_exists('Wolf_Library_Template_Admin')) {
    class Wolf_Library_Template_Admin {
        protected $is_pro_active = false;
        protected $demo_lists = array();

        public function __construct() {
            add_action('admin_menu', array($this, 'create_page_templates'));
        }

        function create_page_templates() {
            add_theme_page(
                esc_html__('Library Templates', 'wolf-library-templates'),
                esc_html__('Library Templates', 'wolf-library-templates'),
                'edit_theme_options',
                'wolf-library-templates',
                array($this, 'page_templates')
           );
        }

        function page_templates() {
            do_action('wolf_before_demo_import_screen');

            echo '<div class="wolf-body">';

            $this->get_header();

            echo '<div class="wolf-content">';
            $this->init_demo_import();
            echo '</div>';

            echo '</div>';

            do_action('wolf_after_demo_import_screen');
        }

        public function get_header() {
            ?>
            <div class='wolf-header'>
                <h1>
                    <?php echo esc_html__('Welcome to the Wolf Library Templates.', 'wolf-library-templates'); ?>
                </h1>
                <p>
                    <?php echo esc_html__('Thank you for choosing us. This quick demo import setup will help you configure your new website like templates demo. It will install the required WordPress plugins, default content and tell you a little about Help &amp; Support options. It should only take less than 5 minutes.', 'wolf-library-templates'); ?>
                </p>
            </div>
            <?php
        }

        public function init_demo_import() {
            $this->demo_lists    = apply_filters('wolf_lt_demo_lists', array());
            $this->is_pro_active = apply_filters('wolf_lt_is_pro_active', $this->is_pro_active);
            $demo_lists          = $this->demo_lists;

            $total_demo = count($demo_lists);

            if ($total_demo >= 1) {
                $this->demo_list($demo_lists, $total_demo);
            }
        }

        public function demo_list($demo_lists, $total_demo) {
            ?>
            <div class="wolf-filter-header">
                <div class="wolf-filter-tabs">
                    <ul class="wolf-types wolf-filter-group" data-filter-group="secondary">
                        <li class="wolf-filter-btn-active wolf-filter-btn wolf-type-filter" data-filter="*">
                            <?php esc_html_e('All', 'wolf-library-templates'); ?>
                            <span class="wolf-count"></span>
                        </li>
                        <?php
                        $types        = array_column($demo_lists, 'type');
                        $unique_types = array_unique($types);
                        foreach ($unique_types as $cat_index => $single_type) {
                            ?>
                            <li class="wolf-filter-btn wolf-type-filter" data-filter=".<?php echo strtolower(esc_attr($single_type)); ?>">
                                <?php echo ucfirst(esc_html($single_type)); ?>
                                <span class="wolf-count"></span>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                    <div class="wolf-search-control">
                        <input id="wolf-filter-search-input" class="wolf-search-filter" type="text" placeholder="<?php esc_attr_e('Search...', 'wolf-library-templates'); ?>">
                    </div>
                </div>
            </div>
            <div class="wolf-filter-content" id="wolf-filter-content">
                <div class="wolf-actions wolf-sidebar">
                    <div class="wolf-import-available-categories">
                        <h3><?php esc_html_e('Categories', 'wolf-library-templates'); ?></h3>
                        <div class="wolf-import-fp-wrap">
                            <ul class="wolf-import-fp-lists wolf-filter-group" data-filter-group="pricing">
                                <li class="wolf-fp-filter wolf-filter-btn wolf-filter-btn-active" data-filter="*">
                                    <?php echo esc_html__('All', 'wolf-library-templates'); ?>
                                </li>
                                <li class="wolf-fp-filter wolf-filter-btn" data-filter=".wolf-fp-filter-free">
                                    <?php echo esc_html__('Free', 'wolf-library-templates'); ?>
                                </li>
                                <li class="wolf-fp-filter wolf-filter-btn" data-filter=".wolf-fp-filter-pro">
                                    <?php echo esc_html__('Pro', 'wolf-library-templates'); ?>
                                </li>
                            </ul>
                        </div>
                        <ul class="wolf-import-available-categories-lists wolf-filter-group" data-filter-group="primary">
                            <li class="wolf-filter-btn-active wolf-filter-btn" data-filter="*">
                                <?php esc_html_e('All Categories', 'wolf-library-templates'); ?>
                                <span class="wolf-count"></span>
                            </li>
                            <?php
                            $categories        = array_column($demo_lists, 'categories');
                            $unique_categories = array();
                            if (is_array($categories) && ! empty($categories)) {
                                foreach ($categories as $demo_index => $demo_cats) {
                                    foreach ($demo_cats as $cat_index => $single_cat) {
                                        if (in_array($single_cat, $unique_categories)) {
                                            continue;
                                        }
                                        $unique_categories[] = $single_cat;
                                        ?>
                                        <li class="wolf-filter-btn" data-filter=".<?php echo strtolower(esc_attr($single_cat)); ?>">
                                            <?php echo ucfirst(esc_html($single_cat)); ?>
                                            <span class="wolf-count"></span>
                                        </li>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </ul>
                    </div>

                </div>
                <div class="wolf-filter-content-wrapper">
                    <?php
                    foreach ($demo_lists as $key => $demo_list) {
                        if (! isset($demo_list['title']) || ! isset($demo_list['screenshot_url']) || ! isset($demo_list['demo_url'])) {
                            continue;
                        }

                        $installed_demo	= Wolf_Demo_Importer_State::get_installed_demo();
                        $class          = [];

                        if ($installed_demo === false) {
                            $class[]	= '';
                        } else {
                            $class[] = $installed_demo == $key ? ' wolf-demo-installed' : ' wolf-demo-disabled';
                        }

                        $class[] = 'wolf-item wolf-demo-' . esc_attr($key);
                        $class[] = isset($demo_list['categories']) ? esc_attr(implode(' ', $demo_list['categories'])) : '';
                        $class[] = isset($demo_list['type']) ? ' ' . esc_attr($demo_list['type']) : '';
                        $class[] = $this->is_pro($demo_list) ? ' wolf-fp-filter-pro' : ' wolf-fp-filter-free';
                        $class[] = $this->is_template_available($demo_list) ? '' : ' wolf-pro-item';

                        ?>
                        <div class="<?php echo implode(' ', $class); ?>">
                            <?php
                            wp_nonce_field('wolf-library-templates');
                            ?>
                            <div class="wolf-item-preview">
                                <div class="wolf-item-screenshot">
                                    <img src="<?php echo esc_url($demo_list['screenshot_url']); ?>">

                                </div>
                                <?php
                                if ($this->is_pro($demo_list)) {
                                    ?>
                                    <span class="wolf-premium-label"><?php esc_html_e('Premium', 'wolf-library-templates'); ?></span>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="wolf-item-footer">
                                <div class="wolf-item-footer_meta">
                                    <h3 class="theme-name"><?php echo esc_html($demo_list['title']); ?></h3>
                                    <div class="wolf-item-footer-actions">
                                        <a class="button wolf-item-demo-link" href="<?php echo esc_url($demo_list['demo_url']); ?>" target="_blank">
                                            <span class="dashicons dashicons-visibility"></span><?php esc_html_e('Preview', 'wolf-library-templates'); ?>
                                        </a>
                                        <?php
                                        echo $this->template_button($demo_list, $key);
                                        ?>
                                    </div>
                                    <div class="wolf-demo-progress-bar-wrapper">
                                        <div class="wolf-demo-progress-bar"></div>
                                    </div>
                                    <?php
                                    $keywords = isset($demo_list['keywords']) ? $demo_list['keywords'] : array();
                                    if (! empty($keywords)) {
                                        echo '<ul class="wolf-keywords hidden">';
                                        foreach ($keywords as $cat_index => $single_keywords) {
                                            ?>
                                            <li class="<?php echo strtolower(esc_attr($single_keywords)); ?>"><?php echo ucfirst(esc_html($single_keywords)); ?></li>
                                            <?php
                                        }
                                        echo '</ul>';
                                    }
                                    ?>

                                </div>

                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }

        public function is_pro($item) {
            $is_pro = false;
            if (isset($item['is_pro']) && $item['is_pro']) {
                $is_pro = true;
            }

            return (bool) apply_filters('advanced_import_is_pro', $is_pro, $item);
        }

        public function template_button($item, $id) {
            ob_start();

            if ($this->is_template_available($item)) {
                $plugins = isset($item['plugins']) && is_array($item['plugins']) ? ' data-plugins="' . esc_attr(wp_json_encode($item['plugins'])) . '"' : '';
                ?>
                <a data-demo-id="<?php echo esc_attr($id); ?>" class="button wolf-button-install-demo button-primary" href="#" <?php echo $plugins; ?>>
                    <?php esc_html_e('Install', 'wolf-library-templates'); ?>
                </a>
                <a href="#" class="button button-primary wolf-button-uninstall-demo" data-demo-id="<?php echo esc_attr($id); ?>">
                    <?php echo esc_html__('Uninstall', 'wolf-library-templates'); ?>
                </a>
                <?php
            } else {
                ?>
                <a class="button button-primary" href="<?php echo esc_url(isset($item['pro_url']) ? $item['pro_url'] : '#'); ?>" target="_blank">
                    <span class="dashicons dashicons-awards"></span><?php esc_html_e('View Pro', 'wolf-library-templates'); ?>
                </a>
                <?php
            }

            $render_button = ob_get_clean();

            $render_button = apply_filters('wolf_lt_template_import_button', $render_button, $item);
            return $render_button;

        }

        public function is_template_available($item) {
            $is_available = false;

            if ($this->is_pro_active) {
                $is_available = true;
            } elseif (! isset($item['is_pro'])) {
                $is_available = true;
            } elseif (isset($item['is_pro']) && ! $item['is_pro']) {
                $is_available = true;
            }

            return (bool) apply_filters('wolf_lt_is_template_available', $is_available, $item);
        }
    }

    new Wolf_Library_Template_Admin();
}