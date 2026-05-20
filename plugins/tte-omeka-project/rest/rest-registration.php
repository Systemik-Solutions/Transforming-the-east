<?php

require_once plugin_dir_path( __FILE__ ) . 'OmekaGeoJsonController.php';

/**
 * Register the rest endponits.
 *
 */
function tte_omeka_project_register_rest_api() {
    $controller = new OmekaGeoJsonController();
    $controller->register_routes();
}

add_action( 'rest_api_init', 'tte_omeka_project_register_rest_api' );
