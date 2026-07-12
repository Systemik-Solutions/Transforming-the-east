<?php
require_once('TTEIAWClient.php');

/**
 * Plugin Name: TTE Annotation
 * Description: Provides Glycerine viewer and annotation for Transforming the East site.
 * Version: 1.1.0
 * Author: Systemik Solutions
 * Author URI: https://www.systemiksolutions.com/
 * Contributors: systemiksolutions, mufengniu
 * Tags: iiif, annotation, glycerine
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: glycerine
 */

//Register the admin page
function tte_embeds_add_admin_page()
{
    add_menu_page(
        'TTE Annotation',                    // Page title
        'TTE Annotation',                    // Menu title
        'manage_options',                    // Capability
        'TTE_Annotation_embeds',              // Menu slug
        'tte_glycerine_embeds_page',          // Function to render the page
        'dashicons-admin-plugins',           // Icon (optional)
        100                                  // Position
    );
}
add_action('admin_menu', 'tte_embeds_add_admin_page');
include_once(plugin_dir_path(__FILE__) . 'includes/admin-settings.php');


function tte_annotation_enqueue_assets()
{

    $force_enqueue = !empty($GLOBALS['tte_force_enqueue']);

    wp_enqueue_style(
        'tte-annotation-main-css',
        plugin_dir_url(__FILE__) . 'assets/css/main.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/main.css')
    );

    // Enqueue jQuery
    wp_enqueue_script(
        'jquery',
        'https://code.jquery.com/jquery-3.6.0.min.js',
        array(),
        null,
        true
    );

    // Enqueue the Glycerine Viewer library
    wp_enqueue_style(
        'tte-annotation-viewer-style',
        'https://unpkg.com/glycerine-viewer@latest/jslib/style.css',
        [],
        null
    );

    wp_enqueue_script(
        'tte-annotation-viewer-lib',
        'https://unpkg.com/glycerine-viewer@latest/jslib/glycerine-viewer.umd.cjs',
        [],
        null,
        true
    );


    if ((is_singular() && has_block('glycerine/text-annotation')) || $force_enqueue) {
        wp_enqueue_style('choices', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css', array(), null);
        wp_enqueue_script('choices', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js', array(), null, true);

        wp_enqueue_script(
            'glycerine-text-annotation-table',
            plugin_dir_url(__FILE__) . 'assets/js/text-annotation.js',
            array('jquery'),
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/text-annotation.js'),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'tte_annotation_enqueue_assets');


function tte_annotation_register_text_annotation_block()
{
    $script_path = plugin_dir_path(__FILE__) . 'build/glycerine-text-annotation.js';
    $asset_path = plugin_dir_path(__FILE__) . 'build/glycerine-text-annotation.asset.php';

    if (!file_exists($script_path)) {
        return;
    }

    $asset = file_exists($asset_path)
        ? require $asset_path
        : array(
            'dependencies' => array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element', 'wp-i18n'),
            'version' => filemtime($script_path),
        );

    wp_register_script(
        'glycerine-text-annotation-block-js',
        plugin_dir_url(__FILE__) . 'build/glycerine-text-annotation.js',
        $asset['dependencies'],
        $asset['version'],
        true
    );

    register_block_type('glycerine/text-annotation', array(
        'editor_script' => 'glycerine-text-annotation-block-js',
        'render_callback' => 'tte_annotation_render_text_annotation_block',
    ));
}
add_action('init', 'tte_annotation_register_text_annotation_block');



function tte_annotation_render_text_annotation_block($attributes)
{
    // Retrieve the selected collection IDs from block attributes
    $selectedCollectionID = isset($attributes['selectedCollectionID']) ? $attributes['selectedCollectionID'] : null;
    $selectedImageSetID = isset($attributes['selectedImageSetID']) ? $attributes['selectedImageSetID'] : null;

    $selected_columns = isset($attributes['selectedColumns']) ? $attributes['selectedColumns'] : [];
    if (empty($selected_columns)) {
        $selected_columns = ['Collection', 'Image Set', 'Annotation Set', 'Title', 'Description (EN)', 'Description (zh_CN)', 'Description (en-Latn)', 'Tags'];
    }
    $imageQuality = isset($attributes['imageQuality']) && is_numeric($attributes['imageQuality']) ? (int)$attributes['imageQuality'] : 600;
    $collection_map = isset($attributes['collectionMap']) ? $attributes['collectionMap'] : [];

    $viewerSettings = isset($attributes['viewerSettings']) ? $attributes['viewerSettings'] : [
        'fullscreenbutton',
        'indexbutton',
        'annotationviewbutton',
        'aboutpanebutton',
        'settingpanebutton',
        'infopanel'
    ];


    if (!$selectedCollectionID && !$selectedImageSetID) {
        return '<div class="glycerine-table"><p>No Image set selected.</p></div>';
    }

    $columnLabelMap = isset($attributes['columnLabelMap']) ? $attributes['columnLabelMap'] : [];


    $block_cache_id = json_encode([
        'selectedCollectionID' => $selectedCollectionID,
        'selectedImageSetID' => $selectedImageSetID,
        'selectedColumns' => $selected_columns,
        'imageQuality' => $imageQuality,
        'collectionMap' => $collection_map,
        'viewerSettings' => $viewerSettings,
        'columnLabelMap' => $columnLabelMap,
    ]);


    $block_cache_id = 'tte_annotation_table_' . substr(md5($block_cache_id), 0, 16);


    // Start output buffering
    ob_start();

    // Prevent rendering inside the block editor
    if (!is_singular() && !is_archive() && !is_home() && !is_front_page()) {
        return;
    }

    $template_path = plugin_dir_path(__FILE__) . 'includes/text-annotation.php';

    if (file_exists($template_path)) {
        include $template_path;
    }

    return ob_get_clean();
}

// Register the API endpoints
function tte_annotation_register_api_endpoints()
{
    register_rest_route('tte_glycerine/v1', '/collections', array(
        'methods' => 'GET',
        'callback' => 'tte_annotation_get_collections',
        'permission_callback' => '__return_true', // Allow public access
    ));

    register_rest_route('tte_glycerine/v1', '/collection/(?P<id>\d+)/image_sets', array(
        'methods' => 'GET',
        'callback' => 'tte_annotation_get_image_sets_from_collection',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('tte_glycerine/v1', '/proxy', [
        'methods' => 'GET',
        'callback' => 'tte_annotation_proxy_request',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'tte_annotation_register_api_endpoints');

function tte_annotation_get_collections()
{
    $api_key = get_option('tte_glycerine_api_key');
    $base_url = get_option('tte_glycerine_iaw_base_url');

    $iawClient = new TTEIAWClient($base_url, $api_key);
    $allCollections = $iawClient->getAllCollections();

    return rest_ensure_response($allCollections);
}


function tte_annotation_get_image_sets_from_collection($request)
{

    $collection_id = $request->get_param('id'); // Extract ID from request
    if (!$collection_id) {
        return [];
    }

    $api_key = get_option('tte_glycerine_api_key');
    $base_url = get_option('tte_glycerine_iaw_base_url');

    $iawClient = new TTEIAWClient($base_url, $api_key);
    $image_sets = $iawClient->searchWithCollection($collection_id, 'image_set', []);

    $res = [];

    foreach ($image_sets as $image_set) {
        if (isset($image_set['publication']) && $image_set['publication']['active'] &&  isset($image_set['publication']['persistent_url'])) {

            $res[] = [
                'id' => $image_set['id'],
                'name' => $image_set['name'],
                'thumbnail' => $image_set['thumbnail'],
                'persistent_url' => $image_set['publication']['persistent_url'],
                'public_url' => str_replace('https://w3id.org/iaw/data/', 'https://w3id.org/iaw/', $image_set['publication']['persistent_url'])
            ];
        }
    }

    return $res;
}


function tte_annotation_proxy_request($request)
{
    $api_key = get_option('tte_glycerine_api_key');

    $target_url = $request->get_param('target');
    if (empty($target_url)) {
        return new WP_Error('missing_target', 'Missing target parameter', ['status' => 400]);
    }


    $response = wp_remote_get($target_url, [
        'timeout' => 30,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Accept' => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('proxy_failed', 'Proxy request failed', ['status' => 500]);
    }

    $body = wp_remote_retrieve_body($response);

    return rest_ensure_response(json_decode($body));
}
