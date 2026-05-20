<?php

$sage_includes = [
    'lib/config.php',    // Config
    'lib/assets.php',    // Scripts and stylesheets
    'lib/extras.php',    // Custom functions
    'lib/setup.php',     // Theme setup
    'lib/titles.php',    // Page titles
    'lib/wrapper.php',   // Theme wrapper class
    'lib/customizer.php', // Theme customizer
    'lib/class-tgm-plugin-activation.php',
    'lib/theme-inc.php',
    'lib/navwalker.php'
];

foreach ($sage_includes as $file) {
    if (!$filepath = locate_template($file)) {
        trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
    }

    require_once $filepath;
}
unset($file, $filepath);


add_action('wp', function () {
    $template = get_page_template_slug();
    if (!$template) {
        $template = basename(get_page_template()); // fallback full file name
    }
    if ($template == 'page-texts.php') {
        add_filter('glycerine/force_enqueue', '__return_true');
    }
});

/**
 * Enqueue the child theme admin styles and scripts.
 */
function tte_omeka_load_admin_theme_scripts()
{
    // Dequeue the parent style first, as the parent theme loads it unconditionally.
    wp_dequeue_style('style');
    $theme = wp_get_theme();
    wp_enqueue_style(
        'usyd-style',
        get_stylesheet_directory_uri() . '/style.css',
        array(),
        $theme->parent()->get('Version')
    );
}
add_action('admin_enqueue_scripts', 'tte_omeka_load_admin_theme_scripts');

/**
 * Enqueue the child theme styles and scripts.
 */
function tte_omeka_load_theme_scripts()
{

    // leaflet map
    wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), null);
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), null, true);

    if (is_page('translations') || is_page('places')) {
        // Load translation map page libraries.
        wp_enqueue_style('choices', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css', array(), null);
        wp_enqueue_script('choices', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js', array(), null, true);
        wp_enqueue_style('rslider', get_stylesheet_directory_uri() . '/css/rSlider.min.css', array(), null);
        wp_enqueue_script('rslider', get_stylesheet_directory_uri() . '/js/rSlider.min.js', array(), null, true);
    }
    // Dequeue the parent style first, as the parent theme loads it unconditionally.
    wp_dequeue_style('style');
    $theme = wp_get_theme();
    wp_enqueue_style(
        'usyd-style',
        get_stylesheet_directory_uri() . '/style.css',
        array(),
        $theme->parent()->get('Version')
    );

    // Load carousel styles and scripts.
    wp_enqueue_style(
        'omaa-carousel',
        get_stylesheet_directory_uri() . '/css/carousel.css',
        array(),
        $theme->get('Version')
    );
    wp_enqueue_script('omaa-carousel', get_stylesheet_directory_uri() . '/js/carousel.js', array(), $theme->get('Version'), true);

    // Pagination.js
    wp_enqueue_style(
        'panigation-js.css',
        'https://cdnjs.cloudflare.com/ajax/libs/paginationjs/2.1.5/pagination.css',
        [],
        null
    );

    wp_enqueue_script(
        'glycerine-viewer.js',
        'https://cdnjs.cloudflare.com/ajax/libs/paginationjs/2.1.5/pagination.min.js',
        [],
        null,
        true
    );

    wp_enqueue_style(
        'tte-style',
        get_stylesheet_uri(),
        array(),
        time()
    );

    wp_enqueue_script(
        'tte-main',
        get_stylesheet_directory_uri() . '/js/main.js',
        array(),
        time(),
        true
    );

    // Load image annotation table styles and scripts.
    wp_enqueue_style(
        'image-annotation-table',
        get_stylesheet_directory_uri() . '/css/image-annotation-table.css',
        [],
        $theme->get('Version')
    );

    wp_enqueue_script(
        'image-cropper',
        'https://platform.readworkbench.org/js/utilities/image-cropper.js',
        [],
        false,
        true
    );

    wp_enqueue_script(
        'image-annotation-table',
        get_stylesheet_directory_uri() . '/js/image-annotation-table.js',
        ['jquery'],
        $theme->get('Version'),
        true
    );

    if (is_page('translations') || is_page('classics') || is_page('persons') || is_page('places')) {
        $script_path = get_stylesheet_directory() . '/js/archive.js';
        $script_url  = get_stylesheet_directory_uri() . '/js/archive.js';

        wp_enqueue_script(
            'omaa-archive',
            $script_url,
            [],
            file_exists($script_path) ? filemtime($script_path) : null,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'tte_omeka_load_theme_scripts', 101);


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
        \TTE\OmekaMap::get('resourceTemplates', 'translation')['id'] => get_stylesheet_directory_uri() . '/image/sigma.js/translation.svg',
        \TTE\OmekaMap::get('resourceTemplates', 'classicalSourceWork')['id'] =>  get_stylesheet_directory_uri() . '/image/sigma.js/book.svg',
        \TTE\OmekaMap::get('resourceTemplates', 'locationHistoryRecord')['id'] => get_stylesheet_directory_uri() . '/image/sigma.js/location.svg',
        \TTE\OmekaMap::get('resourceTemplates', 'place')['id'] => get_stylesheet_directory_uri() . '/image/sigma.js/place.svg',
        \TTE\OmekaMap::get('resourceTemplates', 'person')['id'] => get_stylesheet_directory_uri() . '/image/sigma.js/person.svg'
    ];
    return isset($imageMap[$resourceTemplateID]) ? $imageMap[$resourceTemplateID] : get_stylesheet_directory_uri() . '/image/sigma.js/place.svg';
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
        plugin_dir_url(__FILE__) . '/image/sigma.js/person.svg',
        plugin_dir_url(__FILE__) . '/image/sigma.js/place.svg',
        plugin_dir_url(__FILE__) . '/image/sigma.js/policy-Inquiry-and-report.svg',
        plugin_dir_url(__FILE__) . '/image/sigma.js/newsletter.svg',
        plugin_dir_url(__FILE__) . '/image/sigma.js/community-group.svg',
        plugin_dir_url(__FILE__) . '/image/sigma.js/education.svg'
    ];
}

/**
 * Get the tile render data from a collection of texts.
 *
 *
 * @return array
 */
function tte_omeka_create_translation_tile_data($entities)
{
    $tileData = [];
    foreach ($entities as $entity) {

        $resourceTemplateID = $entity->getResourceTemplateId();
        $resourceID = $entity->getId();

        if ($resourceTemplateID !=  \TTE\OmekaMap::get('resourceTemplates', 'translation')['id']) {
            continue;
        }

        $item = [
            'title' => $entity->getTitle(),
            'url' => tte_omeka_build_entity_url($resourceID, $resourceTemplateID),
            'backgroundColor' => '#393A3B',
            'year' => implode(', ', tte_omeka_get_term_values($entity, 'field_schema_startdate')),
            'language' => implode(', ', tte_omeka_get_term_values($entity, 'field_schema_language')),
            'bookType' => implode(', ', tte_omeka_get_term_values($entity, 'field_te_booktype')),
            'translator' => implode(', ', tte_omeka_get_term_values($entity, 'field_schema_translator')),
        ];

        $tileData[] = $item;
    }

    return $tileData;
}

/**
 * Get the tile render data from a collection of texts.
 *
 *
 * @return array
 */
function tte_omeka_create_classicalSourceWork_tile_data($texts)
{
    $tileData = [];
    foreach ($texts as $text) {

        $resourceTemplateID = $text->getResourceTemplateId();
        $resourceID = $text->getId();

        // if ($resourceTemplateID !=  \TTE\OmekaMap::get('resourceTemplates', 'classicalSourceWork')['id']) {
        //     continue;
        // }

        $item = [
            'title' => $text->getTitle(),
            'url' => tte_omeka_build_entity_url($resourceID, $resourceTemplateID),
            'backgroundColor' => tte_omeka_get_template_attr($resourceTemplateID, 'color'),
        ];

        $tileData[] = $item;
    }

    return $tileData;
}


function tte_omeka_create_story_tile_data($entites)
{
    $tileData = [];
    foreach ($entites as $entity) {

        $resourceTemplateID = $entity->getResourceTemplateId();
        $resourceID = $entity->getId();

        if ($resourceTemplateID !=  \TTE\OmekaMap::get('resourceTemplates', 'story')['id']) {
            continue;
        }

        $item = [
            'title' => $entity->getTitle(),
            'url' => tte_omeka_get_term_values($entity, 'field_schema_url'),
            'backgroundColor' => tte_omeka_get_template_attr($resourceTemplateID, 'color'),
            'author' => tte_omeka_get_term_values($entity, 'field_schema_author'),
            'subtitle' => tte_omeka_get_term_values($entity, 'field_schema_description'),
        ];

        $tileData[] = $item;
    }

    return $tileData;
}


/**
 * Get the tile render data from a collection of texts.
 *
 *
 * @return array
 */
function tte_omeka_create_text_tile_data($texts)
{
    $tileData = [];
    foreach ($texts as $text) {

        $resourceTemplateID = $text->getResourceTemplateId();
        $resourceID = $text->getId();

        if ($resourceTemplateID !=  \TTE\OmekaMap::get('resourceTemplates', 'text')['id']) {
            continue;
        }

        $collectionID = !empty($text->getProperty('field_schema_collection')) ? $text->getProperty('field_schema_collection')[0]['@value'] : null;

        $item = [
            'id' => $resourceID,
            'title' => $text->getTitle(),
            'url' => tte_omeka_build_entity_url($resourceID, $resourceTemplateID),
            'collectionID' => $collectionID,
            'backgroundColor' => tte_omeka_get_template_attr($resourceTemplateID, 'color'),
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
            '<img src="' . ($properties['thumbnail_display_urls']['large']) . '" ' .
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
                        $tableContent .= '<a href="' . tte_omeka_get_entity_url_by_id($item['value_resource_id']) . '" target="_self">' . wp_kses($item['display_title'], $allowed_html) . '</a>';
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
                    $tableContent .= '<a href="' . $item['@id'] . '" target="_blank">' . wp_kses($label, $allowed_html) . '</a>';
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
                    $tableContent .= $item['@value'];
                } else {
                    if (filter_var($item['@value'], FILTER_VALIDATE_URL)) {
                        $tableContent .= '<a href="' . $item['@value'] . '" target="_blank">' . wp_kses($item['@value'], $allowed_html) . '</a>';
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

// 
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

/**
 * Overwrite the base theme breadcrumb function.
 *
 * @return void
 */
function tte_omeka_breadcrumb(
    $isHeuristTranslationPage = null,
    $heuristTranslationPageTitle = null,
    $isClassicalSourceWorkPage = null,
    $heuristclassicalSourceWorkTitle = null,
    $isTextPage = null,
    $heuristTextTitle = null,
    $isPersonPage = null,
    $heuristPersonTitle = null,
    $isEventPage = null,
    $eventTitle = null,
    $isTreatisePage = null,
    $treatiseTitle = null,
    $treatiseTextID = null,
    $isPlacePage = null,
    $placeTitle = null,
    $isStoryPage = null,
    $storyTitle = null,
) {
    /* === OPTIONS === */
    $text['home']     = 'Home'; // text for the 'Home' link
    $text['category'] = 'Archive by Category "%s"'; // text for a category page
    $text['search']   = 'Search Results for "%s" Query'; // text for a search results page
    $text['tag']      = 'Posts Tagged "%s"'; // text for a tag page
    $text['author']   = 'Articles Posted by %s'; // text for an author page
    $text['404']      = 'Error 404'; // text for the 404 page
    $text['page']     = 'Page %s'; // text 'Page N'
    $text['cpage']    = 'Comment Page %s'; // text 'Comment Page N'

    $wrap_before    = '<div class="breadcrumbs breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">'; // the opening wrapper tag
    $wrap_after     = '</div><!-- .breadcrumbs -->'; // the closing wrapper tag
    $sep            = '<span class="breadcrumbs__separator delimiter"> &gt; </span>'; // separator between crumbs
    $before         = '<span class="breadcrumbs__current">'; // tag before the current crumb
    $after          = '</span>'; // tag after the current crumb

    $show_on_home   = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
    $show_home_link = 1; // 1 - show the 'Home' link, 0 - don't show
    $show_current   = 1; // 1 - show current page title, 0 - don't show
    $show_last_sep  = 1; // 1 - show last separator, when current page title is not displayed, 0 - don't show
    /* === END OF OPTIONS === */

    global $post;
    $home_url       = home_url('/');
    $link           = '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
    $link          .= '<a class="breadcrumbs__link" href="%1$s" itemprop="item"><span itemprop="name">%2$s</span></a>';
    $link          .= '<meta itemprop="position" content="%3$s" />';
    $link          .= '</span>';
    $parent_id      = ($post) ? $post->post_parent : '';
    $home_link      = sprintf($link, $home_url, $text['home'], 1);

    if ($isHeuristTranslationPage) {
        // Generate breadcrumbs for Heurist translation page.
        echo $wrap_before;
        echo $home_link;
        echo $sep . sprintf($link, tte_omeka_links('translations'), 'Translations', 0);
        if (!empty($heuristTranslationPageTitle)) {
            echo $sep . $before . esc_html($heuristTranslationPageTitle) . $after;
        }
        echo $wrap_after;
    } else if ($isClassicalSourceWorkPage) {
        // Generate breadcrumbs for Heurist classical source work page.
        echo $wrap_before;
        echo $home_link;
        echo $sep . sprintf($link, tte_omeka_links('classics'), 'Classics', 0);
        if (!empty($heuristclassicalSourceWorkTitle)) {
            echo $sep . $before . esc_html($heuristclassicalSourceWorkTitle) . $after;
        }
        echo $wrap_after;
    } else if ($isTextPage) {
        // Generate breadcrumbs for Heurist text page.
        echo $wrap_before;
        echo $home_link;
        echo $sep . sprintf($link, tte_omeka_links('texts'), 'Texts', 0);

        if (!empty($heuristTextTitle)) {
            echo $sep . $before . esc_html($heuristTextTitle) . $after;
        }
    } else if ($isPlacePage) {
        // Generate breadcrumbs for Heurist place page.
        echo $wrap_before;
        echo $home_link;
        echo $sep . sprintf($link, tte_omeka_links('places'), 'Places', 0);
        if (!empty($placeTitle)) {
            echo $sep . $before . esc_html($placeTitle) . $after;
        }
    } else if ($isStoryPage) {
        // Generate breadcrumbs for Heurist place page.
        echo $wrap_before;
        echo $home_link;
        echo $sep . sprintf($link, tte_omeka_links('stories'), 'Stories', 0);
        if (!empty($storyTitle)) {
            echo $sep . $before . esc_html($storyTitle) . $after;
        }
    } 
    else if ($isTreatisePage) {
        // Generate breadcrumbs for Heurist treatise page.
        echo $wrap_before;
        echo $home_link;
        echo $sep . sprintf($link, tte_omeka_links('texts'), 'Texts', 0);

        echo $sep .  sprintf($link, tte_omeka_build_entity_url($treatiseTextID, \TTE\OmekaMap::get('resourceTemplates', 'text')['id']), $heuristTextTitle, 0);

        if (!empty($treatiseTitle)) {
            echo $sep . $before . esc_html($treatiseTitle) . $after;
        }
    } else if ($isPersonPage) {
        // Generate breadcrumbs for Heurist text page.
        echo $wrap_before;
        echo $home_link;
        echo $sep . sprintf($link, tte_omeka_links('persons'), 'Persons', 0);
        if (!empty($heuristPersonTitle)) {
            echo $sep . $before . esc_html($heuristPersonTitle) . $after;
        }
    } else if ($isEventPage) {
        // Generate breadcrumbs for Heurist text page.
        echo $wrap_before;
        echo $home_link;
        if (!empty($eventTitle)) {
            echo $sep . '<span><a class="breadcrumbs__link" href="/category/events/" itemprop="item"><span itemprop="name">Events</span></a><meta itemprop="position" content="0"></span> <span class="breadcrumbs__separator delimiter"> &gt; </span> ' .
                '<span class="breadcrumbs__current">' .  esc_html($eventTitle)  . '</span>'
                . $after;
        }
    } else if (is_home() || is_front_page()) {
        if ($show_on_home) echo $wrap_before . $home_link . $wrap_after;
    } else {
        $position = 0;

        echo $wrap_before;

        if ($show_home_link) {
            $position += 1;
            echo $home_link;
        }

        if (is_category()) {
            $parents = get_ancestors(get_query_var('cat'), 'category');
            foreach (array_reverse($parents) as $cat) {
                $position += 1;
                if ($position > 1) {
                    echo $sep;
                }
                echo sprintf($link, get_category_link($cat), get_cat_name($cat), $position);
            }
            if (get_query_var('paged')) {
                $position += 1;
                $cat      = get_query_var('cat');
                echo $sep . sprintf($link, get_category_link($cat), get_cat_name($cat), $position);
                echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_current) {
                    if ($position >= 1) {
                        echo $sep;
                    }
                    echo $before . sprintf($text['category'], single_cat_title('', false)) . $after;
                } elseif ($show_last_sep) {
                    echo $sep;
                }
            }
        } elseif (is_search()) {
            if ($show_home_link && $show_current || !$show_current && $show_last_sep) {
                echo $sep;
            }
            if ($show_current) {
                echo $before . sprintf($text['search'], get_search_query()) . $after;
            }
        } elseif (is_year()) {
            if ($show_home_link && $show_current) {
                echo $sep;
            }
            if ($show_current) {
                echo $before . get_the_time('Y') . $after;
            } elseif ($show_home_link && $show_last_sep) {
                echo $sep;
            }
        } elseif (is_month()) {
            if ($show_home_link) {
                echo $sep;
            }
            $position += 1;
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'), $position);
            if ($show_current) {
                echo $sep . $before . get_the_time('F') . $after;
            } elseif ($show_last_sep) {
                echo $sep;
            }
        } elseif (is_day()) {
            if ($show_home_link) {
                echo $sep;
            }
            $position += 1;
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'), $position) . $sep;
            $position += 1;
            echo sprintf($link, get_month_link(get_the_time('Y'), get_the_time('m')), get_the_time('F'), $position);
            if ($show_current) {
                echo $sep . $before . get_the_time('d') . $after;
            } elseif ($show_last_sep) {
                echo $sep;
            }
        } elseif (is_attachment()) {
            $parent    = get_post($parent_id);
            $cat       = get_the_category($parent->ID);
            $catID     = $cat[0]->cat_ID;
            $parents   = get_ancestors($catID, 'category');
            $parents   = array_reverse($parents);
            $parents[] = $catID;
            foreach ($parents as $cat) {
                $position += 1;
                if ($position > 1) {
                    echo $sep;
                }
                echo sprintf($link, get_category_link($cat), get_cat_name($cat), $position);
            }
            $position += 1;
            echo $sep . sprintf($link, get_permalink($parent), $parent->post_title, $position);
            if ($show_current) {
                echo $sep . $before . get_the_title() . $after;
            } elseif ($show_last_sep) {
                echo $sep;
            }
        } elseif (is_page() && !$parent_id) {
            if ($show_home_link && $show_current) {
                echo $sep;
            }
            if ($show_current) {
                echo $before . get_the_title() . $after;
            } elseif ($show_home_link && $show_last_sep) {
                echo $sep;
            }
        } elseif (is_page() && $parent_id) {
            $parents = get_post_ancestors(get_the_ID());
            foreach (array_reverse($parents) as $pageID) {
                $position += 1;
                if ($position > 1) {
                    echo $sep;
                }
                echo sprintf($link, get_page_link($pageID), get_the_title($pageID), $position);
            }
            if ($show_current) {
                echo $sep . $before . get_the_title() . $after;
            } elseif ($show_last_sep) {
                echo $sep;
            }
        } elseif (is_tag()) {
            if (get_query_var('paged')) {
                $position += 1;
                $tagID    = get_query_var('tag_id');
                echo $sep . sprintf($link, get_tag_link($tagID), single_tag_title('', false), $position);
                echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_home_link && $show_current) {
                    echo $sep;
                }
                if ($show_current) {
                    echo $before . sprintf($text['tag'], single_tag_title('', false)) . $after;
                } elseif ($show_home_link && $show_last_sep) {
                    echo $sep;
                }
            }
        } elseif (is_tax()) {
            if ($show_home_link && $show_current) {
                echo $sep;
            }
            if ($show_current) {
                echo $before . single_term_title('', false) . $after;
            }
        } elseif (is_author()) {
            $author = get_userdata(get_query_var('author'));
            if (get_query_var('paged')) {
                $position += 1;
                echo $sep . sprintf($link, get_author_posts_url($author->ID), sprintf($text['author'], $author->display_name), $position);
                echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_home_link && $show_current) {
                    echo $sep;
                }
                if ($show_current) {
                    echo $before . sprintf($text['author'], $author->display_name) . $after;
                } elseif ($show_home_link && $show_last_sep) {
                    echo $sep;
                }
            }
        } elseif (is_404()) {
            if ($show_home_link && $show_current) {
                echo $sep;
            }
            if ($show_current) {
                echo $before . $text['404'] . $after;
            } elseif ($show_last_sep) {
                echo $sep;
            }
        } elseif (has_post_format() && !is_singular()) {
            if ($show_home_link && $show_current) {
                echo $sep;
            }
            echo get_post_format_string(get_post_format());
        }

        echo $wrap_after;
    }
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
