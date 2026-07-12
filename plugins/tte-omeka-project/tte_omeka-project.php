<?php

/**
 * Plugin Name:       TTE-OMEKA Project
 * Description:       Plugin for TTE site
 * Version: 1.1.0
 * Author:            Systemik Solutions
 */

require_once plugin_dir_path(__FILE__) . 'config.php';
require_once plugin_dir_path(__FILE__) . 'src/OmekaMap.php';
require_once plugin_dir_path(__FILE__) . 'src/OmekaSearch.php';
require_once plugin_dir_path(__FILE__) . 'src/OmekaEntity.php';
require_once plugin_dir_path(__FILE__) . 'src/OmekaClassicalSourceWork.php';
require_once plugin_dir_path(__FILE__) . 'src/OmekaClassicalSourceWorkSection.php';
require_once plugin_dir_path(__FILE__) . 'src/OmekaTranslation.php';
require_once plugin_dir_path(__FILE__) . 'src/OmekaTranslationSection.php';
require_once plugin_dir_path(__FILE__) . 'src/OmekaVocabulary.php';
require_once plugin_dir_path(__FILE__) . 'rest/rest-registration.php';

/**
 * Register the URL route for Heurist entities.
 */
function tte_omeka_project_register_heurist_entity_route()
{
    add_rewrite_rule('translation/([a-z0-9-]+)[/]?$', 'index.php?translationID=$matches[1]', 'top');
    add_rewrite_rule('classical-source-work/([a-z0-9-]+)[/]?$', 'index.php?classicsID=$matches[1]', 'top');
    add_rewrite_rule('person/([a-z0-9-]+)[/]?$', 'index.php?personID=$matches[1]', 'top');
    add_rewrite_rule('place/([a-z0-9-]+)[/]?$', 'index.php?placeID=$matches[1]', 'top');

    add_rewrite_rule('story/([a-z0-9-]+)[/]?$', 'index.php?storyID=$matches[1]', 'top');


    // Treatise page: /text/{textID}/treatise/{treatiseID}
    add_rewrite_rule(
        '^text/([0-9]+)/treatise/([0-9]+)/?$',
        'index.php?textID=$matches[1]&treatiseID=$matches[2]',
        'top'
    );

    // Text page: /text/{textID}
    add_rewrite_rule(
        '^text/([a-z0-9-]+)/?$',
        'index.php?textID=$matches[1]',
        'top'
    );
}

add_action('init', 'tte_omeka_project_register_heurist_entity_route');

/**
 * Register Heurist entity ID in URL parameters.
 */
function tte_omeka_project_register_heurist_entity_id_parameter($query_vars)
{
    $query_vars[] = 'translationID';
    $query_vars[] = 'classicsID';
    $query_vars[] = 'personID';
    $query_vars[] = 'placeID';
    $query_vars[] = 'textID';
    $query_vars[] = 'treatiseID';
    $query_vars[] = 'storyID';

    return $query_vars;
}

add_filter('query_vars', 'tte_omeka_project_register_heurist_entity_id_parameter');

/**
 * Register the template for Heurist entity.
 */
function tte_omeka_project_register_heurist_entity_page_template($template)
{
    if (get_query_var('translationID') !== false && get_query_var('translationID') !== '') {
        $located = locate_template('translation-single.php');
        if (!empty($located)) {
            return get_stylesheet_directory() . '/translation-single.php';
        }
    } else if (get_query_var('classicsID') !== false && get_query_var('classicsID') !== '') {
        $located = locate_template('classical-source-work-single.php');
        if (!empty($located)) {
            return get_stylesheet_directory() . '/classical-source-work-single.php';
        }
    } else if (get_query_var('textID') !== false && get_query_var('textID') !== '') {

        if (get_query_var('treatiseID') !== false && get_query_var('treatiseID') !== '') {
            $GLOBALS['tte_force_enqueue'] = true;
            $located = locate_template('treatise-single.php');
            if (!empty($located)) {
                return get_stylesheet_directory() . '/treatise-single.php';
            }
        } else {
            $located = locate_template('text-single.php');
            if (!empty($located)) {
                return get_stylesheet_directory() . '/text-single.php';
            }
        }
    } else if (get_query_var('personID') !== false && get_query_var('personID') !== '') {
        $located = locate_template('person-single.php');
        if (!empty($located)) {
            return get_stylesheet_directory() . '/person-single.php';
        }
    } else if (get_query_var('placeID') !== false && get_query_var('placeID') !== '') {
        $located = locate_template('place-single.php');
        if (!empty($located)) {
            return get_stylesheet_directory() . '/place-single.php';
        }
    }else if (get_query_var('storyID') !== false && get_query_var('storyID') !== '') {
        $located = locate_template('story-single.php');
        if (!empty($located)) {
            return get_stylesheet_directory() . '/story-single.php';
        }
    }
    if (is_page('texts')) {
        $GLOBALS['glycerine_force_enqueue'] = true;
    }

    return $template;
}

add_action('template_include', 'tte_omeka_project_register_heurist_entity_page_template');




add_action('admin_menu', function () {
    add_menu_page(
        'TTE Cache',
        'Clear TTE Cache',
        'manage_options',
        'tte_clear_cache',
        'tte_omeka_clear_cache_callback',
        'dashicons-trash',
        90
    );
});
function tte_omeka_clear_cache_callback()
{
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }

    $deleted = 0;

    if (isset($_POST['clear_cache']) && check_admin_referer('tte_clear_cache_action')) {
        global $wpdb;

        $rules = [
            ['tte_annotation_glycerine_', 'tte_annotation_table_'],
            ['tte_omeka_cached_request_json_'],
            ['tte_omeka_cached_resource_template_label_'],
            ['tte_cached_entity_link_'],
        ];

        $deleted_keys = 0;

        // Build SQL LIKE filters to fetch candidate option_names fast
        $wheres = [];
        $params = [];

        foreach ($rules as $parts) {
            foreach ($parts as $part) {
                $wheres[] = "option_name LIKE %s";
                $params[] = '%' . $wpdb->esc_like($part) . '%';
            }
        }

        // Only transient-related rows
        $sql = "
        SELECT option_name
        FROM {$wpdb->options}
        WHERE (option_name LIKE %s OR option_name LIKE %s)
          AND (" . implode(' OR ', $wheres) . ")
    ";

        array_unshift(
            $params,
            $wpdb->esc_like('_transient_') . '%',
            $wpdb->esc_like('_transient_timeout_') . '%'
        );


        $option_names = $wpdb->get_col($wpdb->prepare($sql, $params));
        $option_names = array_unique($option_names);

        $keys = [];

        foreach ($option_names as $option_name) {
            $key = preg_replace('/^_transient_timeout_/', '', $option_name);
            $key = preg_replace('/^_transient_/', '', $key);
            $keys[$key] = true;
        }

        foreach (array_keys($keys) as $key) {
            $match = false;
            foreach ($rules as $parts) {
                $ok = true;
                foreach ($parts as $part) {
                    if (strpos($key, $part) === false) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    $match = true;
                    break;
                }
            }
            if (!$match) continue;

            // Delete the whole transient entry (value + timeout)
            delete_transient($key);

            // Hard cleanup in DB (in case delete_transient doesn't fully remove rows)
            $wpdb->delete($wpdb->options, ['option_name' => '_transient_' . $key]);
            $wpdb->delete($wpdb->options, ['option_name' => '_transient_timeout_' . $key]);

            $deleted_keys++;
        }

        echo "<div class='updated'><p>Cache cleared successfully! Deleted {$deleted_keys} entries.</p></div>";
    }

?>
    <div class="wrap">
        <h1>Clear TTE Cache</h1>
        <form method="post">
            <?php wp_nonce_field('tte_clear_cache_action'); ?>
            <p><input type="submit" name="clear_cache" class="button button-primary" value="Clear Cache"></p>
        </form>
    </div>
<?php
}


function tte_omeka_create_featured_record_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'featured_records';
    $charset_collate = $wpdb->get_charset_collate();

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            entity_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
add_action('init', 'tte_omeka_create_featured_record_table');
