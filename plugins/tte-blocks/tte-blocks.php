<?php

/**
 * Plugin Name:       TTE Blocks
 * Description:       Custom Gutenberg blocks for TTE - provides featured item carousel
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           1.1.0
 * Author:            Systemik Solutions
 * Author URI:        https://systemiksolutions.com/
 * License:           GPLv2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tte-blocks
 *
 * @package           tte-blocks
 */

require_once plugin_dir_path(__FILE__) . 'render/omeka-feature.php';


/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function tte_blocks_block_init()
{
	register_block_type(__DIR__ . '/build/feature', [
		'render_callback' => 'tte_blocks_render_home_block_feature',
	]);
}

add_action('init', 'tte_blocks_block_init');

/**
 * Register the block category.
 *
 * @param $categories
 *
 * @return mixed
 */
function tte_blocks_register_block_category($categories)
{
	// Adding a new category.
	$category = array(
		'slug'  => 'tte-blocks',
		'title' => 'tte Blocks'
	);
	array_unshift($categories, $category);

	return $categories;
}

add_filter('block_categories_all', 'tte_blocks_register_block_category');
