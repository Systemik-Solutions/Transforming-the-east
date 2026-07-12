<?php

/**
 * Plugin Name: TTE Theme
 * Description: Custom theme for tte PROJECT
 * Version: 1.1.0
 * Author: Systemik Solutions
 * Author URI: https://systemiksolutions.com/
 * License: GPLv2 or later
 * Text Domain: tte-theme
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TTE_THEME_PLUGIN_FILE', __FILE__);
define('TTE_THEME_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TTE_THEME_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once TTE_THEME_PLUGIN_DIR . 'functions.php';

/**
 * The public entity URLs need a real WordPress page so a block theme can render
 * the normal USYDBRAND header/footer around plugin content.
 */
function tte_theme_activate()
{
    if (!get_page_by_path('tte-record')) {
        wp_insert_post([
            'post_title'   => 'TTE Record',
            'post_name'    => 'tte-record',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
            'meta_input'   => ['_tte_virtual_page' => 1],
        ]);
    }

    tte_theme_register_routes();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'tte_theme_activate');

function tte_theme_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'tte_theme_deactivate');

function tte_theme_register_routes()
{
    $routes = [
        'translation'          => 'translationID',
        'classical-source-work' => 'classicsID',
        'person'               => 'personID',
        'place'                => 'placeID',
        'story'                => 'storyID',
    ];

    foreach ($routes as $slug => $query_var) {
        add_rewrite_rule(
            $slug . '/([a-z0-9-]+)[/]?$',
            'index.php?pagename=tte-record&' . $query_var . '=$matches[1]',
            'top'
        );
    }

    add_rewrite_rule(
        '^text/([0-9]+)/treatise/([0-9]+)/?$',
        'index.php?pagename=tte-record&textID=$matches[1]&treatiseID=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        '^text/([a-z0-9-]+)/?$',
        'index.php?pagename=tte-record&textID=$matches[1]',
        'top'
    );
}
// Register after tte-omeka-project so these matching rule keys overwrite its
// legacy theme-template targets with the plugin-owned virtual page targets.
add_action('init', 'tte_theme_register_routes', 99);

function tte_theme_register_query_vars($query_vars)
{
    foreach (['translationID', 'classicsID', 'personID', 'placeID', 'storyID', 'textID', 'treatiseID'] as $query_var) {
        if (!in_array($query_var, $query_vars, true)) {
            $query_vars[] = $query_var;
        }
    }

    return $query_vars;
}
add_filter('query_vars', 'tte_theme_register_query_vars');

function tte_theme_entity_template()
{
    $templates = [
        'translationID' => 'translation-single.php',
        'classicsID'    => 'classical-source-work-single.php',
        'personID'      => 'person-single.php',
        'placeID'       => 'place-single.php',
        'storyID'       => 'story-single.php',
    ];

    if (get_query_var('textID')) {
        return get_query_var('treatiseID') ? 'treatise-single.php' : 'text-single.php';
    }

    foreach ($templates as $query_var => $template) {
        if (get_query_var($query_var)) {
            return $template;
        }
    }

    return null;
}

function tte_theme_page_template()
{
    $pages = [
        'translations' => 'page-translations.php',
        'classics'     => 'page-classics.php',
        'texts'        => 'page-texts.php',
        'persons'      => 'page-persons.php',
        'places'       => 'page-places.php',
        'stories'      => 'page-stories.php',
        'philosophia-sinica-tribus-tractatibus' => 'page-philosophia-sinica-tribus-tractatibus.php',
    ];

    foreach ($pages as $slug => $template) {
        if (is_page($slug)) {
            return $template;
        }
    }

    return null;
}

function tte_theme_is_plugin_page()
{
    return (bool) (tte_theme_entity_template() || tte_theme_page_template());
}

function tte_theme_render_content($content)
{
    static $rendering = false;

    if ($rendering || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $template = tte_theme_entity_template();
    if (!$template) {
        $template = tte_theme_page_template();
    }

    if (!$template || !file_exists(TTE_THEME_PLUGIN_DIR . $template)) {
        return $content;
    }

    $rendering = true;
    ob_start();
    include TTE_THEME_PLUGIN_DIR . $template;
    $custom_content = ob_get_clean();
    $rendering = false;

    return '<div class="tte-project">' . $custom_content . '</div>';
}
add_filter('the_content', 'tte_theme_render_content', 20);

function tte_theme_hide_virtual_page_title($block_content, $block)
{
    if (is_page('tte-record') && ($block['blockName'] ?? '') === 'core/post-title') {
        return '';
    }

    return $block_content;
}
add_filter('render_block', 'tte_theme_hide_virtual_page_title', 10, 2);

function tte_theme_virtual_page_robots($robots)
{
    if (is_page('tte-record')) {
        $robots['noindex'] = true;
    }

    return $robots;
}
add_filter('wp_robots', 'tte_theme_virtual_page_robots');

function tte_theme_document_title($parts)
{
    $template = tte_theme_entity_template();
    if (!$template) {
        return $parts;
    }

    // A treatise ID belongs to Glycerine, not Omeka. On treatise pages, use
    // the parent Omeka text for the document title.
    $query_vars = ['translationID', 'classicsID', 'personID', 'placeID', 'storyID', 'textID'];
    foreach ($query_vars as $query_var) {
        $entity_id = get_query_var($query_var);
        if (!$entity_id) {
            continue;
        }

        $entity = \TTE\OmekaEntity::load($entity_id);
        if ($entity && $entity->getTitle()) {
            $parts['title'] = $entity->getTitle();
        }
        break;
    }

    return $parts;
}
add_filter('document_title_parts', 'tte_theme_document_title', 20);

/**
 * Replace the internal renderer-page breadcrumb with the public Omeka trail.
 */
function tte_theme_filter_breadcrumb_items($items)
{
    if (!is_page('tte-record')) {
        return $items;
    }

    $route_map = [
        'translationID' => ['label' => 'Translations', 'url' => home_url('/translations/')],
        'classicsID'    => ['label' => 'Classics', 'url' => home_url('/classics/')],
        'personID'      => ['label' => 'Persons', 'url' => home_url('/persons/')],
        'placeID'       => ['label' => 'Places', 'url' => home_url('/places/')],
        'storyID'       => ['label' => 'Stories', 'url' => home_url('/stories/')],
    ];

    $custom_items = [];

    if (get_query_var('textID')) {
        $text_id = get_query_var('textID');
        $text = \TTE\OmekaEntity::load($text_id);
        $text_title = $text && $text->getTitle() ? $text->getTitle() : __('Text', 'tte-theme');

        $custom_items[] = ['label' => __('Texts', 'tte-theme'), 'url' => home_url('/texts/')];

        if (get_query_var('treatiseID')) {
            $custom_items[] = [
                'label' => $text_title,
                'url'   => home_url('/text/' . rawurlencode($text_id) . '/'),
            ];
            $custom_items[] = [
                'label' => sprintf(__('Treatise %s', 'tte-theme'), get_query_var('treatiseID')),
            ];
        } else {
            $custom_items[] = ['label' => $text_title];
        }
    } else {
        foreach ($route_map as $query_var => $archive) {
            $entity_id = get_query_var($query_var);
            if (!$entity_id) {
                continue;
            }

            $entity = \TTE\OmekaEntity::load($entity_id);
            $entity_title = $entity && $entity->getTitle()
                ? $entity->getTitle()
                : __('Record', 'tte-theme');

            $custom_items[] = $archive;
            $custom_items[] = ['label' => $entity_title];
            break;
        }
    }

    if (empty($custom_items)) {
        return $items;
    }

    $home_items = array_values(array_filter($items, static function ($item) {
        return !empty($item['url'])
            && untrailingslashit($item['url']) === untrailingslashit(home_url('/'));
    }));

    return array_merge($home_items, $custom_items);
}
add_filter('block_core_breadcrumbs_items', 'tte_theme_filter_breadcrumb_items');

function tte_theme_enqueue_assets()
{
    $is_plugin_page = tte_theme_is_plugin_page();
    $is_home_page = is_front_page() || is_page('home');

    if (!$is_plugin_page && !$is_home_page) {
        return;
    }

    $version = '1.0.0';


    $home_archive_path = TTE_THEME_PLUGIN_DIR . 'css/main.css';
    $home_carousel_path = TTE_THEME_PLUGIN_DIR . 'css/home.css';
    wp_enqueue_style('tte-home-archive', TTE_THEME_PLUGIN_URL . 'css/main.css', [], filemtime($home_archive_path));
    wp_enqueue_style('tte-home-carousel', TTE_THEME_PLUGIN_URL . 'css/home.css', ['tte-home-archive'], filemtime($home_carousel_path));


    if (!$is_plugin_page) {
        return;
    }

    wp_enqueue_script('jquery');
    wp_enqueue_style('tte-main-css', TTE_THEME_PLUGIN_URL . 'css/main.css', [], $version);
    wp_enqueue_script('tte-theme-main', TTE_THEME_PLUGIN_URL . 'js/main.js', ['jquery'], $version, true);
    wp_localize_script('tte-theme-main', 'tteTheme', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'featuredNonce' => wp_create_nonce('tte_update_featured_record'),
    ]);

    // Load global styles.
    wp_enqueue_style('source-sans-pro-font', 'https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600&display=swap', array(), null);
    $styles_path = plugin_dir_path(__FILE__) . 'dist/styles/';
    $styles_url = plugin_dir_url(__FILE__) . 'dist/styles/';
    foreach (glob($styles_path . '*.css') as $file) {
        $filename = basename($file);
        wp_enqueue_style(
            'plugin-style-' . $filename,
            $styles_url . $filename
        );
    }

    wp_enqueue_style(
        'tte-font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css',
        [],
        '4.7.0'
    );
    wp_enqueue_style('tte-choices', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css', [], null);
    wp_enqueue_script('tte-choices', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js', [], null, true);
    wp_enqueue_style('tte-rslider', TTE_THEME_PLUGIN_URL . 'css/rSlider.min.css', [], $version);
    wp_enqueue_script('tte-rslider', TTE_THEME_PLUGIN_URL . 'js/rSlider.min.js', [], $version, true);
    wp_enqueue_style('tte-pagination', 'https://cdnjs.cloudflare.com/ajax/libs/paginationjs/2.1.5/pagination.css', [], '2.1.5');
    wp_enqueue_script('tte-pagination', 'https://cdnjs.cloudflare.com/ajax/libs/paginationjs/2.1.5/pagination.min.js', ['jquery'], '2.1.5', true);
    wp_enqueue_script('tte-theme-archive', TTE_THEME_PLUGIN_URL . 'js/archive.js', ['jquery', 'tte-pagination'], $version, true);


    if (is_page('places') || get_query_var('placeID')) {
        wp_enqueue_style('tte-leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
        wp_enqueue_script('tte-leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
    }

    if (is_page('philosophia-sinica-tribus-tractatibus') || get_query_var('treatiseID')) {
        wp_enqueue_style('tte-choices', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css', [], null);
        wp_enqueue_script('tte-choices', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js', [], null, true);
    }

    if (tte_theme_entity_template()) {
        wp_enqueue_style('tte-image-annotation-table', TTE_THEME_PLUGIN_URL . 'css/image-annotation-table.css', [], $version);
        wp_enqueue_script('tte-image-cropper', 'https://platform.readworkbench.org/js/utilities/image-cropper.js', [], null, true);
        wp_enqueue_script('tte-image-annotation-table', TTE_THEME_PLUGIN_URL . 'js/image-annotation-table.js', ['jquery', 'tte-image-cropper'], $version, true);
    }
}
add_action('wp_enqueue_scripts', 'tte_theme_enqueue_assets', 20);

function tte_theme_hide_virtual_page_from_search($query)
{
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        $meta_query = (array) $query->get('meta_query');
        $meta_query[] = [
            'key'     => '_tte_virtual_page',
            'compare' => 'NOT EXISTS',
        ];
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'tte_theme_hide_virtual_page_from_search');
