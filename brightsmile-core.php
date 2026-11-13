<?php
/**
 * Plugin Name: BrightSmile Core
 * Description: BrightSmile Core plugin.
 * Version: 1.0.0
 * Author: Amit Shukla
 *
 * @package BrightSmile_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Service post type and the Service Type taxonomy.
 */
function brightsmile_register() {
	register_post_type(
		'service',
		array(
			'labels'       => array(
				'name'          => 'Services',
				'singular_name' => 'Service',
			),
			'public'       => true,
			'has_archive'  => true,
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-hammer',
		)
	);

	register_taxonomy(
		'service_type',
		'service',
		array(
			'labels'       => array(
				'name'          => 'Service Types',
				'singular_name' => 'Service Type',
			),
			'hierarchical' => true,
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'brightsmile_register' );
