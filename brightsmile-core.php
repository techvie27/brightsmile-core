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

/**
 * Flush permalinks when the plugin is activated.
 */
function brightsmile_activate() {
	brightsmile_register();
	brightsmile_register_doctor_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'brightsmile_activate' );


/**
 * Register the Appointment custom post type.
 *
 * @return void
 */
function brightsmile_register_appointment_cpt() {
	$labels = array(
		'name'          => __( 'Appointments', 'brightsmile' ),
		'singular_name' => __( 'Appointment', 'brightsmile' ),
	);

	$args = array(
		'labels'        => $labels,
		'public'        => false,
		'show_ui'       => true,
		'show_in_rest'  => false,
		'supports'      => array( 'title' ),
		'menu_icon'     => 'dashicons-calendar-alt',
	);

	register_post_type( 'appointment', $args );
}
add_action( 'init', 'brightsmile_register_appointment_cpt' );

/**
 * Display the appointment booking form.
 *
 * @return string
 */
function brightsmile_appointment_form_shortcode() {
	$services = get_posts(
		array(
			'post_type'      => 'service',
			'posts_per_page' => -1,
		)
	);

	ob_start();
	?>

	<form method="post">
		<p>
			<label for="brightsmile_name">Name:</label>
			<input type="text" id="brightsmile_name" name="brightsmile_name">
		</p>

		<p>
			<label for="brightsmile_phone">Phone:</label>
			<input type="tel" id="brightsmile_phone" name="brightsmile_phone">
		</p>

		<p>
			<label for="brightsmile_treatment">Treatment:</label>
			<select id="brightsmile_treatment" name="brightsmile_treatment">
				<option value="">Select a treatment</option>

				<?php foreach ( $services as $service ) : ?>
					<option value="<?php echo esc_attr( $service->ID ); ?>">
						<?php echo esc_html( $service->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="brightsmile_date">Preferred date:</label>
			<input type="date" id="brightsmile_date" name="brightsmile_date">
		</p>

		<button type="submit" name="brightsmile_submit">
			Book appointment
		</button>
	</form>

	<?php
	return ob_get_clean();
}
add_shortcode( 'brightsmile_appointment_form', 'brightsmile_appointment_form_shortcode' );
