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

/**
 * Tell ACF to save field groups as JSON files in this plugin.
 *
 * @return string Folder path.
 */
function brightsmile_acf_json_save_point() {
	return plugin_dir_path( __FILE__ ) . 'acf-json';
}
add_filter( 'acf/settings/save_json', 'brightsmile_acf_json_save_point' );

/**
 * Tell ACF to also load field groups from the same folder.
 *
 * @param array $paths Folders ACF already loads from.
 * @return array Folders with ours added.
 */
function brightsmile_acf_json_load_point( $paths ) {
	$paths[] = plugin_dir_path( __FILE__ ) . 'acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'brightsmile_acf_json_load_point' );


/**
 * Register the Doctor custom post type.
 *
 * @return void
 */
function brightsmile_register_doctor_cpt() {
	$labels = array(
		'name'          => __( 'Doctors', 'brightsmile' ),
		'singular_name' => __( 'Doctor', 'brightsmile' ),
	);

	$args = array(
		'labels'        => $labels,
		'public'        => true,
		'show_in_rest'  => true,
		'has_archive'   => false,
		'supports'      => array( 'title', 'editor', 'thumbnail' ),
		'menu_icon'     => 'dashicons-id',
	);

	register_post_type( 'doctor', $args );
}
add_action( 'init', 'brightsmile_register_doctor_cpt' );
