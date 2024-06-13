<?php

require_once plugin_dir_path( __FILE__ ) . 'HeuristRequestController.php';

/**
 * Register the rest endponits.
 *
 */
function heurist_api_register_rest_api() {
    $requestController = new HeuristRequestController();
    $requestController->register_routes();
}

add_action( 'rest_api_init', 'heurist_api_register_rest_api' );
