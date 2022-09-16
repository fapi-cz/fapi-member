<?php
/**
 * Plugin Name:       FAPI Member
 * Description:       FAPI Member block and extensions.
 * Requires at least: 5.8
 * Requires PHP:      5.6
 * Version:           0.1.2
 * Author:            FAPI Business s.r.o.
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fapi-member
 *
 * @package           fapi-member
 */

function create_block_fapi_member_block_init() {

	$blocks = array(
		'block-extender/',
		'fapi-form-block/',
	);

	foreach( $blocks as $block ) {
		register_block_type( __DIR__ . '/includes/block-editor/blocks/' . $block );
	}
}
add_action( 'init', 'create_block_fapi_member_block_init' );

function register_fapi_category( $categories ) {

	$categories[] = array(
		'slug'  => 'fapi',
		'title' => __('FAPI', 'fapi-member')
	);

	return $categories;
}

if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
	add_filter( 'block_categories_all', 'register_fapi_category' );
} else {
	add_filter( 'block_categories', 'register_fapi_category' );
}
