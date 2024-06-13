<?php

class HeuristRequestController extends WP_REST_Controller {

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        $version = '1';
        $namespace = 'heurist-api/v' . $version;
        $base = 'request';
        register_rest_route($namespace, '/' . $base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'sendRequest'],
                'permission_callback' => '__return_true',
            ],
        ]);
    }

    /**
     * Controller method of sending request to Heurist.
     *
     * @param WP_REST_Request $request
     *   The request parameters must contain the following:
     *   - host: The heurist host name without the tailing slash.
     *   - db: The Heurist DB name.
     *   - path: The Heurist request path starting with the slash.
     *   All other parameters will be used as the request data.
     *
     * @return WP_REST_Response
     */
    public function sendRequest(WP_REST_Request $request) {
        $parameters = $request->get_params();
        $response = null;
        if (isset($parameters['host']) && isset($parameters['db']) && isset($parameters['path'])) {
            $client = new \HeuristAPI\HeuristClient($parameters['host'], $parameters['db']);
            $path = $parameters['path'];
            unset($parameters['host']);
            unset($parameters['db']);
            unset($parameters['path']);
            $response = $client->get($path, $parameters);
            if ($response) {
                return new WP_REST_Response($response, 200);
            }
        }
        return new WP_REST_Response($response, 404);
    }
}
