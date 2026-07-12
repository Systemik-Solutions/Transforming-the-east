<?php

class OmekaGeoJsonController extends WP_REST_Controller
{

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        $version = '1';
        $namespace = 'tte-omeka-api/v' . $version;
        $base = 'omeka-geojson';
        register_rest_route($namespace, '/' . $base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'sendRequest'],
                'permission_callback' => '__return_true',
                'args' => [
                    'type' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return in_array($param, ['cluster', 'timeLine']);
                        },
                    ],
                ],
            ],
        ]);
    }

    /**
     * Controller method of sending request to Heurist.
     * 
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
    public function sendRequest(WP_REST_Request $request)
    {

        $mapType = $request->get_param('type');
        $mapEntities =  tte_omeka_get_all_searched_entities($_GET['hq'], [
            'resource_template_id' => \TTE\OmekaMap::get('resourceTemplates', 'translation')['id'],
            'per_page' => 10000,
        ]);

        $featuredEntities = \TTE\OmekaEntity::search([
            'has_features' => '1',
            'per_page' => 99999,
            'is_public' => 1
        ], true)['entities'];

        $idToPlace = [];
        foreach ($featuredEntities as $entity) {
            $mappingFeature = $entity->getProperty('field_o_module_mapping_feature');
            foreach ($mappingFeature as $feature) {
                if ($feature['o-module-mapping:geography-type'] === 'Point') {
                    $idToPlace[$entity->getId()] = [
                        'title' => $entity->getTitle(),
                        'location' => [
                            'type' => 'Point',
                            'coordinates' => $feature['o-module-mapping:geography-coordinates'],
                        ]
                    ];
                }
            }
        }

        $data['listMap'] = [
            'type' => 'FeatureCollection',
            'metadata' => [
                'name' => 'Textus Omeka records',
                'description' => 'This is the map of records from Textus Omeka database',
            ],
            'features' => [],
            'display' => [
                "info" => [
                    "logo" => get_stylesheet_directory_uri() . '/image/textus_icon_logo.png',
                    "content" => "<div><p>Click a pin to see its details.</p></div>"
                ],
                "basemapGallery" => false,
                "basemap" => "hybrid",
                "clusterColor" => "#393A3B",
                "clusterFontColor" => "white",
                "popup" => [
                    'allowedFields' => [],
                    'content' => "<p>{year}</p> <p>{language}</p> <p>{bookType}</p> <p>{translator}</p>  <a target='_top' href={linkback}>Read more</a>"
                ]
            ],
        ];

        $data['timeMap'] = $data['listMap'];
        $data['journeyMap'] = $data['listMap'];
        $data['journeyMap']['display']['info']['content'] = "<div><p>Click a journey to see its details.</p></div>";


        foreach ($mapEntities as $entity) {

            $id = $entity->getId();
            $resourceTemplateID = $entity->getResourceTemplateId();

            $foundPlace = false;
            $placeName = null;
            $placeLocation = null;

            $entityProperties = $entity->getProperties();
            foreach ($entityProperties as $propName => $propValues) {
                if (substr($propName, 0, 6) === 'field_') {
                    foreach ($propValues as $propValue) {
                        if (isset($propValue['type']) &&  $propValue['type'] === 'resource:item') {
                            $relatedResourceId = $propValue['value_resource_id'];
                            if (isset($idToPlace[$relatedResourceId])) {
                                $foundPlace = true;
                                $placeName = $idToPlace[$relatedResourceId]['title'];
                                $placeLocation = $idToPlace[$relatedResourceId]['location'];
                                break 2; // Exit both loops
                            }
                        }
                    }
                }
            }

            if (!$foundPlace) {
                continue;
            }

            $backgroundColor = tte_omeka_get_template_attr($resourceTemplateID, 'color');
            $year = tte_omeka_get_date_year('field_schema_startdate', $entity)[0];
            $language =   implode(', ', tte_omeka_get_term_values($entity, 'field_schema_language'));
            $bookType =  implode(', ', tte_omeka_get_term_values($entity, 'field_te_booktype'));
            $translator =  implode(', ', tte_omeka_get_term_values($entity, 'field_schema_translator'));

            if ($foundPlace) {
                //ListMap
                $data['listMap']['features'][] = [
                    'type' => 'Feature',
                    'geometry' => $placeLocation,
                    'properties' => [
                        'name' =>  $entity->getTitle(),
                        'type' => tte_omeka_get_template_attr($resourceTemplateID, 'name'),
                        'placename' => $placeName,
                        'linkback' => get_site_url(null, tte_omeka_get_template_attr($resourceTemplateID, 'slug') . '/' . $id),
                        'year' => $year,
                        'language' => $language,
                        'bookType' => $bookType,
                        'translator' => $translator,
                    ],
                    'display' => [
                        'color' => $backgroundColor,
                    ]
                ];

                //TimeMap
                if (isset($year)) {


                    $data['timeMap']['features'][] = [
                        'type' => 'Feature',
                        'geometry' => $placeLocation,
                        'properties' => [
                            'name' =>  $entity->getTitle(),
                            'type' => tte_omeka_get_template_attr($resourceTemplateID, 'name'),
                            'placename' => $placeName,
                            'linkback' => get_site_url(null, tte_omeka_get_template_attr($resourceTemplateID, 'slug') . '/' . $id),
                            'year' => $year,
                            'language' => $language,
                            'bookType' => $bookType,
                            'translator' => $translator,
                            'datestart' => $year,
                            'dateend' => $year,
                            'udatestart' => $year ? ((int) strtotime($year) * 1000) : null,
                            'udateend' => $year ? ((int) strtotime($year) * 1000) : null,
                        ],
                        'display' => [
                            'color' => $backgroundColor,
                        ]
                    ];
                }
            }
        }


        if ($mapType === 'cluster') {
            return new WP_REST_Response($data['listMap'], 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            ]);
        } else if ($mapType === 'timeLine') {
            return new WP_REST_Response($data['timeMap'], 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            ]);
        }
    }
}
