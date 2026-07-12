<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp', function () {
    if (is_page('texts') || get_query_var('textID')) {
        add_filter('glycerine/force_enqueue', '__return_true');
    }
});

/**
 * Load a template part from this plugin without relying on the active theme.
 */
function tte_omeka_plugin_get_template_part($template, $part = null, $args = [])
{
    $template_path = TTE_THEME_PLUGIN_DIR . 'templates/' . $template;
    if ($part) {
        $template_path .= '-' . $part;
    }
    $template_path .= '.php';

    if (!file_exists($template_path)) {
        trigger_error(
            sprintf(esc_html__('Error locating %s for inclusion', 'tte-theme'), esc_html($template_path)),
            E_USER_WARNING
        );
        return;
    }

    include $template_path;
}


/**
 * Extracts an array of term values from an Omeka entity.
 *
 * @param object $entity The Omeka entity object.
 * @param string $fieldName The property name (e.g., 'field_schema_keywords').
 * @return array An array of string values from the given field.
 */
function tte_omeka_get_term_values($entity, $fieldName)
{
    $values = [];

    if (!$entity) {
        return $values;
    }

    $fieldValues = $entity->getProperty($fieldName);

    if (!empty($fieldValues) && is_array($fieldValues)) {
        foreach ($fieldValues as $valueItem) {
            if (!empty($valueItem['@value'])) {
                $values[] = $valueItem['@value'];
            } else if (!empty($valueItem['display_title'])) {
                $values[] = $valueItem['display_title'];
            }
        }
    }
    return $values;
}

function tte_omeka_get_template_attr($resourceTemplateID, $attribute)
{
    // Get the whole list of resource templates
    $resourceTemplateMap = \TTE\OmekaMap::getResourceTemplateMap();

    foreach ($resourceTemplateMap as $template) {
        if ((string)$template['id'] == (string)$resourceTemplateID) {
            return $template[$attribute] ?? "Unknown";
        }
    }

    return "Unknown";
}

function tte_omeka_get_image_link($resourceTemplateID)
{
    $imageMap = [
        \TTE\OmekaMap::get('resourceTemplates', 'translation')['id'] => TTE_THEME_PLUGIN_URL . 'image/sigma.js/translation.svg',
        \TTE\OmekaMap::get('resourceTemplates', 'classicalSourceWork')['id'] => TTE_THEME_PLUGIN_URL . 'image/sigma.js/book.svg',
        \TTE\OmekaMap::get('resourceTemplates', 'locationHistoryRecord')['id'] => TTE_THEME_PLUGIN_URL . 'image/sigma.js/location.svg',
        \TTE\OmekaMap::get('resourceTemplates', 'place')['id'] => TTE_THEME_PLUGIN_URL . 'image/sigma.js/place.svg',
        \TTE\OmekaMap::get('resourceTemplates', 'person')['id'] => TTE_THEME_PLUGIN_URL . 'image/sigma.js/person.svg'
    ];
    return isset($imageMap[$resourceTemplateID]) ? $imageMap[$resourceTemplateID] : TTE_THEME_PLUGIN_URL . 'image/sigma.js/place.svg';
}

/**
 * Get the  year from a date field of an entity.
 *
 * @param string $fieldName
 *   The internal field name.
 * @param OmekaEntity $entity
 *   The Omeka entity.
 *
 * @return array
 *   The year(s) extracted from the date field
 */

function tte_omeka_get_date_year($fieldName, $entity)
{

    $dateItems = $entity->getProperty($fieldName);

    $years = [];
    foreach ($dateItems as $dateItem) {
        if ($dateItem['type'] != 'numeric:timestamp') {
            continue;
        }
        $date = $dateItem['@value'];
        $parts = explode('-', $date);
        $year = (int) $parts[0];
        if (!in_array($year, $years)) {
            $years[] = $year;
        }
    }
    return $years;
}


function tte_omeka_get_sigma_network_icons()
{
    return [
        TTE_THEME_PLUGIN_URL . 'image/sigma.js/person.svg',
        TTE_THEME_PLUGIN_URL . 'image/sigma.js/place.svg',
        TTE_THEME_PLUGIN_URL . 'image/sigma.js/image.svg',
        TTE_THEME_PLUGIN_URL . 'image/sigma.js/newspaper.svg',
        TTE_THEME_PLUGIN_URL . 'image/sigma.js/text.svg',
        TTE_THEME_PLUGIN_URL . 'image/sigma.js/translation.svg'
    ];
}


function tte_omeka_create_entity_tile_data($entities)
{
    $tileData = [];
    foreach ($entities as $entity) {

        $resourceTemplateID = $entity->getResourceTemplateId();
        $resourceID = $entity->getId();

        $item = [
            'id' => $resourceID,
            'resourceTemplateID' => $entity->getResourceTemplateId(),
            'title' => $entity->getTitle(),
            'url' => tte_omeka_build_entity_url($resourceID, $resourceTemplateID),
            'collectionID' => !empty($entity->getProperty('field_schema_collection')) ? $entity->getProperty('field_schema_collection')[0]['@value'] : null,
            'backgroundColor' => tte_omeka_get_template_attr($resourceTemplateID, 'color'),
            'author' => tte_omeka_get_term_values($entity, 'field_schema_author'),
            'date' => tte_omeka_get_term_values($entity, 'field_schema_datecreated'),
            'year' => implode(', ', tte_omeka_get_term_values($entity, 'field_schema_startdate')),
            'language' => implode(', ', tte_omeka_get_term_values($entity, 'field_schema_language')),
            'bookType' => implode(', ', tte_omeka_get_term_values($entity, 'field_te_booktype')),
            'translator' => implode(', ', tte_omeka_get_term_values($entity, 'field_schema_translator')),
            'subtitle' => tte_omeka_get_term_values($entity, 'field_schema_description'),
        ];

        $tileData[] = $item;
    }

    return $tileData;
}

/**
 * Helper function to get a link URL by name.
 *
 * @param string $name
 *   The link name.
 *
 * @return string
 *   The link URL.
 */
function tte_omeka_links($name)
{
    $links = [
        'translations' => '/translations',
        'classics' => '/classics',
        'texts' => '/texts',
        'persons' => '/persons',
        'places' => '/places',
        'stories' => '/stories'
    ];
    return isset($links[$name]) ? $links[$name] : '#';
}

/**
 * Check whether a web page can be embedded as iframe.
 *
 * @param string $url
 *   The page URL.
 *
 * @return bool
 */
function tte_omeka_check_url_iframe_support($url)
{
    // Check if the URL uses HTTPS
    if (stripos($url, 'https://') !== 0) {
        return false;
    }

    $headers = get_headers($url, true);
    if ($headers) {

        // Handling redirections and checking if all are HTTPS
        if (isset($headers['Location'])) {
            $locations = is_array($headers['Location']) ? $headers['Location'] : [$headers['Location']];
            foreach ($locations as $location) {
                if (stripos($location, 'https://') !== 0) {
                    return false;  // Any redirect is not HTTPS, do not support
                }
            }
        }

        $responseCode = tte_omeka_get_http_response_code_from_headers($headers);
        if ($responseCode === '200') {

            $iframeSupport = true;
            foreach ($headers as $header => $value) {
                if (strtolower($header) === 'x-frame-options') {
                    $iframeSupport = false;
                    break;
                }
            }

            if (strpos($url, 'books.google.') !== false) {
                $parsedUrl = parse_url($url);
                parse_str($parsedUrl['query'], $queryParams);
                if (isset($queryParams['id'])) {
                    return "https://books.google.it/books?id=" . $queryParams['id'] . "&hl=it&pg=PP5&output=embed";
                }
            }

            return $iframeSupport ? $url : false;
        }
    }
    return false;
}

function tte_omeka_get_http_response_code_from_headers($headers)
{
    $code = null;
    foreach ($headers as $name => $value) {
        if (is_numeric($name)) {
            $code = substr($value, 9, 3);
        }
    }
    return $code;
}

/**
 * Get the table HTML for translation page.
 *
 * @return string
 *   The HTML markup.
 */
function tte_omeka_get_table_display($properties, $includeMetaNames = [], $excludeMetaNames = [], $tableWidth = '20%', $showThumbnailOnTop = false, $isResourcesSection = false)
{
    $tableContent = "<table style='margin:auto ; width:100%' class='mt-4 mb-4'><tbody>";

    $allowed_html = array(
        'strong'     => array(),
        'br'         => array(),
        'a'          => array(
            'href'   => array(),
            'title'  => array(),
            'target' => array(),
            'rel'    => array()
        ),
        'blockquote' => array(),
        'pre'        => array(),
        'code'       => array()
    );

    if ($showThumbnailOnTop  && !empty($properties['thumbnail_display_urls'])) {
        $tableContent .= '<tr><td style="width:' . $tableWidth . ';font-weight:bold">Thumbnail</td><td>';

        $tableContent .=
            '<img src="' . esc_url($properties['thumbnail_display_urls']['large']) . '" ' .
            'class="img-fluid mr-2 mb-2" />';

        $tableContent .= '</td></tr>';
    }


    foreach ($properties as $propName => $propValue) {
        if (substr($propName, 0, 6) === 'field_') {

            if (!empty($includeMetaNames) && !in_array($propName, $includeMetaNames)) {
                continue;
            }

            if (!empty($excludeMetaNames) && in_array($propName, $excludeMetaNames)) {
                continue;
            }

            $rowCount = count($propValue);
            $isFirst = true;

            foreach ($propValue as $item) {

                $tableContent .= '<tr>';

                if ($isFirst) {
                    $formattedLabel = tte_omeka_format_property_label($item['property_label']);
                    $tableContent .= '<td rowspan="' . $rowCount . '" style="width:' . $tableWidth . ';font-weight:bold">' .
                        wp_kses($formattedLabel, $allowed_html) . '</td>';
                    $isFirst = false;
                }

                $tableContent .= '<td>';


                if ($item['type'] === 'resource:item') {
                    if (tte_omeka_get_entity_url_by_id($item['value_resource_id']) != null) {
                        $tableContent .= '<a href="' . esc_url(tte_omeka_get_entity_url_by_id($item['value_resource_id'])) . '" target="_self">' . wp_kses($item['display_title'], $allowed_html) . '</a>';
                    } else {

                        if ($item['property_id'] == TTE\OmekaMap::get('propertyIDs', 'field_te_copylocation')['id']) {
                            // For location History Record item , append the call number (field_te_callnumber)
                            $locationHistoryRecord = TTE\OmekaEntity::load($item['value_resource_id']);
                            if ($locationHistoryRecord->getResourceTemplateId() != TTE\OmekaMap::get('resourceTemplates', 'locationHistoryRecord')['id']) {
                                $tableContent .= wp_kses($item['display_title'], $allowed_html);
                            } else {

                                $callNumbers = $locationHistoryRecord->getProperty('field_te_callnumber');
                                $callNumberStr = '';

                                if (!empty($callNumbers)) {
                                    $callNumberStr = '. Call number: ' . $callNumbers[0]['@value'];
                                }
                                $tableContent .= wp_kses($item['display_title'] . $callNumberStr, $allowed_html);
                            }
                        } else {
                            $tableContent .= wp_kses($item['display_title'], $allowed_html);
                        }
                    }
                } elseif ($item['type'] === 'uri') {
                    $label = !empty($item['o:label']) && $item['o:label'] != '' ? $item['o:label'] : $item['@id'];
                    $tableContent .= '<a href="' . esc_url($item['@id']) . '" target="_blank" rel="noopener noreferrer">' . wp_kses($label, $allowed_html) . '</a>';
                } elseif (
                    $item['type'] === 'resource:media'
                    && !empty($item['thumbnail_type'])
                ) {
                    $thumbType = $item['thumbnail_type'];
                    if (str_starts_with($thumbType, 'image')) {
                        $tableContent .=
                            '<img src="' . esc_url($item['thumbnail_url']) . '" ' .
                            'alt="' . esc_attr($item['thumbnail_title'] ?? '') . '" ' .
                            'class="img-fluid" />';
                    } elseif (str_ends_with($thumbType, 'pdf')) {
                        $mediaInfoLink = $item['@id'];
                        $mediaResponse = wp_remote_get($mediaInfoLink);
                        $mediaData = json_decode(wp_remote_retrieve_body($mediaResponse), true);
                        $pdfUrl = $mediaData['o:original_url'] ?? null;

                        if ($pdfUrl) {
                            if ($isResourcesSection) {
                                $linkTitle = 'View PDF';
                                $tableContent .= sprintf(
                                    '<a href="%s"  target="_blank" rel="noopener noreferrer" class="digitisation-pdf-link">📄 %s</a>',
                                    esc_url($pdfUrl),
                                    esc_html($linkTitle)
                                );
                            } else {
                                $tableContent .=
                                    '<iframe src="' . esc_url($pdfUrl) . '" ' .
                                    'style="width:100%; height:500px; border:1px solid #ddd;" ' .
                                    'title="' . esc_attr($item['thumbnail_title'] ?? 'PDF document') . '">' .
                                    '</iframe>';
                            }
                        }
                    }
                } elseif ($item['type'] === 'numeric:timestamp') {

                    $raw = trim((string) ($item['@value'] ?? ''));

                    $date = $raw; // default fallback

                    // BC formats:
                    // -YYYY-MM-DD, -YYYY-MM, -YYYY
                    if (preg_match('/^-(\d{1,4})(?:-(\d{2})(?:-(\d{2}))?)?$/', $raw, $m)) {
                        $year  = (int) $m[1];              // e.g. 551
                        $month = $m[2] ?? null;            // "06"
                        $day   = $m[3] ?? null;            // "14"

                        if ($month && $day) {
                            // 14 June 551 BC
                            $monthName = date('F', mktime(0, 0, 0, (int)$month, 1));
                            $date = (int)$day . ' ' . $monthName . ' ' . $year . ' BC';
                        } elseif ($month) {
                            // June 551 BC
                            $monthName = date('F', mktime(0, 0, 0, (int)$month, 1));
                            $date = $monthName . ' ' . $year . ' BC';
                        } else {
                            // 551 BC
                            $date = $year . ' BC';
                        }

                        // AD formats:
                    } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m)) {
                        // YYYY-MM-DD → 14 June 1716
                        $monthName = date('F', mktime(0, 0, 0, (int)$m[2], 1));
                        $date = (int)$m[3] . ' ' . $monthName . ' ' . $m[1];
                    } elseif (preg_match('/^(\d{4})-(\d{2})$/', $raw, $m)) {
                        // YYYY-MM → June 1716
                        $monthName = date('F', mktime(0, 0, 0, (int)$m[2], 1));
                        $date = $monthName . ' ' . $m[1];
                    } elseif (preg_match('/^\d{4}$/', $raw)) {
                        // YYYY → 1716
                        $date = $raw;
                    }

                    $tableContent .= wp_kses($date, $allowed_html);
                } else if ($item['type'] === 'literal') {
                    $tableContent .= wp_kses($item['@value'], $allowed_html);
                } else if ($item['type'] === 'html') {
                    $tableContent .= wp_kses_post($item['@value']);
                } else {
                    if (filter_var($item['@value'], FILTER_VALIDATE_URL)) {
                        $tableContent .= '<a href="' . esc_url($item['@value']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($item['@value']) . '</a>';
                    } else {
                        $tableContent .= wp_kses($item['@value'], $allowed_html);
                    }
                }

                if (!empty($item['@annotation']) && is_array($item['@annotation'])) {
                    $annotationHtml = '';
                    foreach ($item['@annotation'] as $annotationValues) {
                        if (!is_array($annotationValues)) {
                            continue;
                        }
                        foreach ($annotationValues as $annotationItem) {
                            if (empty($annotationItem['property_label'])) {
                                continue;
                            }
                            $annotationLabel = tte_omeka_format_property_label($annotationItem['property_label']);
                            $annotationValue = $annotationItem['@value'] ?? '';
                            $annotationHtml .=
                                '<div class="property">' .
                                '<dt>' . wp_kses($annotationLabel, $allowed_html) . '</dt>' .
                                '<dd class="value" lang=""><span class="value-content">' .
                                wp_kses($annotationValue, $allowed_html) .
                                '</span></dd>' .
                                '</div>';
                        }
                    }
                    if ($annotationHtml !== '') {
                        $tableContent .= '<dl class="table-annotation">' . $annotationHtml . '</dl>';
                    }
                }

                $tableContent .= '</td></tr>';
            }
        }
    }

    $tableContent .= '</tbody></table>';
    return $tableContent;
}


function tte_omeka_build_entity_url($entityID, $resourceTemplateID)
{
    $slug = tte_omeka_get_template_attr($resourceTemplateID, 'slug');

    if (empty($slug) || $slug === 'Unknown') {
        return null;
    }

    return get_site_url(null, $slug . '/' . $entityID);
}

// Resolve an entity URL from its ID and optional resource template.
function tte_omeka_get_entity_url_by_id($entityID, $resourceTemplateID = null)
{
    if (empty($entityID)) {
        return null;
    }

    if ($resourceTemplateID !== null) {
        return tte_omeka_build_entity_url($entityID, $resourceTemplateID);
    }

    // --------------------------------------------------
    // Cached lookup
    // --------------------------------------------------
    $cacheKey = 'tte_cached_entity_link_' . (int) $entityID;
    $cached = get_transient($cacheKey);
    if ($cached !== false) {
        return $cached;
    }

    // --------------------------------------------------
    // Load entity only if needed
    // --------------------------------------------------
    $entity = \TTE\OmekaEntity::load($entityID);
    if (!$entity) {
        return null;
    }

    $resourceTemplateID = $entity->getResourceTemplateId();
    $resourceURL = tte_omeka_build_entity_url($entityID, $resourceTemplateID);

    // Cache indefinitely (until manual invalidation)
    set_transient($cacheKey, $resourceURL, 0);

    return $resourceURL;
}


function tte_omeka_format_property_label($label)
{
    // Replace camelCase or PascalCase with spaces
    $label = preg_replace('/(?<!^)([A-Z])/', ' $1', $label);

    // Replace underscores/dots with spaces
    $label = str_replace(['_', '.'], ' ', $label);

    // Capitalize words
    return ucwords($label);
}

// Get all searched entities based on the search query and base parameters.
function tte_omeka_get_all_searched_entities($hq, $baseParams)
{
    $searchQuery = [];
    if (isset($hq)) {
        $hqDecoded = rawurldecode($hq);
        parse_str($hqDecoded, $searchQuery);
    }
    $searchQuery = array_merge($searchQuery, $baseParams);
    $mapEntities = \TTE\OmekaEntity::search($searchQuery, true)['entities'] ?? [];

    return $mapEntities;
}

/**
 *  Get the nodes and arrays for the sigma graph.
 * 
 */
function tte_omeka_get_sigma_node_graph($entities)
{
    // Initialize arrays for nodes and edges
    $nodes = [];
    $addedNodes = [];
    $edges = [];
    $legendMap = [];
    $allowedTypes = [
        \TTE\OmekaMap::get('resourceTemplates', 'translation')['id'],
        \TTE\OmekaMap::get('resourceTemplates', 'classicalSourceWork')['id'],
        \TTE\OmekaMap::get('resourceTemplates', 'locationHistoryRecord')['id'],
        \TTE\OmekaMap::get('resourceTemplates', 'place')['id'],
        \TTE\OmekaMap::get('resourceTemplates', 'person')['id'],
    ];

    $legendMap = [
        'Translation' => [
            'color' => tte_omeka_get_template_attr(\TTE\OmekaMap::get('resourceTemplates', 'translation')['id'], 'color'),
            'url'   => tte_omeka_get_image_link(\TTE\OmekaMap::get('resourceTemplates', 'translation')['id']),
        ],
        'Person' => [
            'color' => tte_omeka_get_template_attr(\TTE\OmekaMap::get('resourceTemplates', 'person')['id'], 'color'),
            'url'   => tte_omeka_get_image_link(\TTE\OmekaMap::get('resourceTemplates', 'person')['id']),
        ],
        'Place' => [
            'color' => tte_omeka_get_template_attr(\TTE\OmekaMap::get('resourceTemplates', 'place')['id'], 'color'),
            'url'   => tte_omeka_get_image_link(\TTE\OmekaMap::get('resourceTemplates', 'place')['id']),
        ],
        'Classical Source Work' => [
            'color' => tte_omeka_get_template_attr(\TTE\OmekaMap::get('resourceTemplates', 'classicalSourceWork')['id'], 'color'),
            'url'   => tte_omeka_get_image_link(\TTE\OmekaMap::get('resourceTemplates', 'classicalSourceWork')['id']),
        ],
        'Location History Record' => [
            'color' => tte_omeka_get_template_attr(\TTE\OmekaMap::get('resourceTemplates', 'locationHistoryRecord')['id'], 'color'),
            'url'   => tte_omeka_get_image_link(\TTE\OmekaMap::get('resourceTemplates', 'locationHistoryRecord')['id']),
        ]
    ];

    // Build nodes and legendMap
    foreach ($entities as $entity) {

        $resourceTemplateID = $entity->getProperty('o:resource_template')['o:id'];
        if (!in_array($resourceTemplateID, $allowedTypes)) {
            continue;
        }

        $id = $entity->getId();
        $title = $entity->getTitle();

        $label = $title;
        if (strlen($label) > 25) {
            $label = mb_substr($label, 0, 22, 'UTF-8') . '...';
        }

        $sigmaNode = [
            'id' =>  $id,
            'label' => $label,
            'title' => $label,
            'x' => random_int(-585, 585),
            'y' => random_int(-350, 350),
            'size' => 5,
            'color' => tte_omeka_get_template_attr($resourceTemplateID, 'color'),
            'keyperiodColor' => tte_omeka_get_template_attr($resourceTemplateID, 'color'),
            "url" => tte_omeka_get_image_link($resourceTemplateID),
            "type" => "image",
            'name' => $label,
            'fullName' => $title,
            'link' => tte_omeka_build_entity_url($id, $resourceTemplateID),
            'year' => implode(', ', tte_omeka_get_term_values($entity, 'field_schema_startdate')),
            'language' => implode(', ', tte_omeka_get_term_values($entity, 'field_schema_language')),
            'bookType' => implode(', ', tte_omeka_get_term_values($entity, 'field_te_booktype')),
            'translator' => implode(', ', tte_omeka_get_term_values($entity, 'field_schema_translator')),
        ];
        $addedNodes[] = $sigmaNode['id'];
        $nodes[] = $sigmaNode;
    }

    // Convert edges
    $nodeDegree = [];
    $minDegree = PHP_INT_MAX;
    $maxDegree = PHP_INT_MIN;
    $index = 0;
    foreach ($entities as $entity) {
        $sourceID = $entity->getId();

        // 1. Loop through all fields starting with 'field_'
        foreach ($entity->getProperties() as $fieldName => $fieldValues) {
            if (!str_starts_with($fieldName, 'field_')) {
                continue;
            }

            // 2. Check if first value is of type 'resource'
            if (!is_array($fieldValues) || !isset($fieldValues[0]['type']) || $fieldValues[0]['type'] !== 'resource:item') {
                continue;
            }

            // 3. Loop through each resource value
            foreach ($fieldValues as $fieldValue) {
                if (!isset($fieldValue['value_resource_id'])) {
                    continue;
                }

                $targetID = $fieldValue['value_resource_id'];

                // Avoid duplicate edges (if source not added or target already added)
                if (!in_array($sourceID, $addedNodes) || !in_array($targetID, $addedNodes)) {
                    continue;
                }

                $sigmaEdge = [
                    'id' => $index++,
                    'source' => $sourceID,
                    'target' => $targetID,
                    'size' => 8,
                    'color' => '#ccc',
                ];
                $edges[] = $sigmaEdge;

                // Update degree counters
                $nodeDegree[$sourceID] = ($nodeDegree[$sourceID] ?? 0) + 1;
                $nodeDegree[$targetID] = ($nodeDegree[$targetID] ?? 0) + 1;

                // Update min/max degrees
                $minDegree = min($minDegree, $nodeDegree[$sourceID], $nodeDegree[$targetID]);
                $maxDegree = max($maxDegree, $nodeDegree[$sourceID], $nodeDegree[$targetID]);
            }
        }
    }

    // Update size of node based on number of degrees
    foreach ($nodes as &$node) {
        $node['degree'] = isset($nodeDegree[$node['id']]) ? $nodeDegree[$node['id']] : 0;
        if ($maxDegree - $minDegree == 0) {
            $node['size'] = 3;
        } else {
            $node['size'] = abs(3 + 15 * (($node['degree'] - $minDegree) / ($maxDegree - $minDegree)));
        }
    }
    unset($node); // Always unset reference variable after loop


    // --------------------------------------------------
    // Remove isolated nodes (degree === 0)
    // --------------------------------------------------
    $nodes = array_values(array_filter($nodes, function ($node) {
        return !empty($node['degree']);
    }));


    $res = [
        'nodes' => $nodes,
        'edges' => $edges,
        'legendMap' => $legendMap,
    ];
    return $res;
}



function tte_omeka_check_whether_entity_is_featured($entity_id)
{
    global $wpdb;

    $id = (int) $entity_id;
    if ($id <= 0) {
        return false;
    }

    $table  = $wpdb->prefix . 'featured_records';
    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT EXISTS(SELECT 1 FROM {$table} WHERE entity_id = %d)",
            $id
        )
    );

    return (bool) $exists; // returns true/false
}


/**
 * AJAX: Add or remove a entity_id in the featured_records table.
 * Expects:
 *  - action_type: 'add' | 'remove'
 *  - entity_id: integer
 */
function tte_omeka_ajax_update_featured_record()
{
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'You do not have permission to perform this action.'], 403);
    }

    check_ajax_referer('tte_update_featured_record', 'nonce');

    // Validate inputs
    $action_type = isset($_POST['action_type']) ? sanitize_key($_POST['action_type']) : '';
    $entity_id  = isset($_POST['entity_id']) ? intval($_POST['entity_id']) : 0;

    if (!in_array($action_type, ['add', 'remove'], true)) {
        wp_send_json_error(['message' => 'Invalid action_type. Use "add" or "remove".'], 400);
    }
    if ($entity_id <= 0) {
        wp_send_json_error(['message' => 'Invalid entity_id.'], 400);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'featured_records';

    if ($action_type === 'add') {
        // Avoid duplicates (table doesn’t have a UNIQUE index on entity_id)
        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$table} WHERE entity_id = %d LIMIT 1", $entity_id)
        );

        if ($exists) {
            wp_send_json_success(['message' => 'Record already featured.', 'status' => 'exists', 'entity_id' => $entity_id]);
        }

        $ok = $wpdb->insert($table, ['entity_id' => $entity_id], ['%d']);
        if ($ok === false) {
            wp_send_json_error(['message' => 'Database insert failed.'], 500);
        }

        wp_send_json_success(['message' => 'Record featured successfully.', 'status' => 'added', 'entity_id' => $entity_id]);
    }

    // action_type === 'remove'
    $deleted = $wpdb->delete($table, ['entity_id' => $entity_id], ['%d']);
    if ($deleted === false) {
        wp_send_json_error(['message' => 'Database delete failed.'], 500);
    }

    // If 0 rows affected, it just wasn’t there—still a success for idempotency
    wp_send_json_success([
        'message'    => $deleted ? 'Record unfeatured successfully.' : 'Record was not featured.',
        'status'     => $deleted ? 'removed' : 'not_found',
        'entity_id' => $entity_id
    ]);
}
add_action('wp_ajax_tte_update_featured_record', 'tte_omeka_ajax_update_featured_record');



function tte_omeka_get_all_featured_entities()
{
    global $wpdb;
    $table = $wpdb->prefix . 'featured_records';
    $results = $wpdb->get_col("SELECT entity_id FROM {$table}");
    $featured_ids = array_map('intval', $results);

    if (empty($featured_ids)) {
        return [];
    }

    $query = [
        'id' => implode(',', $featured_ids),
        'per_page' => count($featured_ids),
        'is_public' => 1
    ];

    $featuredEntities = \TTE\OmekaEntity::search($query, true)['entities'];
    return $featuredEntities;
}
