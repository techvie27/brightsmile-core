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
	add_role(
		'brightsmile_receptionist',
		'Receptionist',
		array(
			'read'                          => true,
			'brightsmile_view_appointments' => true,
		)
	);

	$brightsmile_admin_role = get_role( 'administrator' );
	if ( $brightsmile_admin_role ) {
		$brightsmile_admin_role->add_cap( 'brightsmile_view_appointments' );
	}
}
register_activation_hook( __FILE__, 'brightsmile_activate' );

/**
 * Add the Appointments page to the admin menu.
 *
 * @return void
 */
function brightsmile_add_appointments_page() {
	add_menu_page(
		'Appointments',
		'Appointments',
		'brightsmile_view_appointments',
		'brightsmile-appointments',
		'brightsmile_appointments_page_html',
		'dashicons-calendar-alt',
		26
	);
}
add_action( 'admin_menu', 'brightsmile_add_appointments_page' );

/**
 * Show the Appointments admin page.
 *
 * @return void
 */
function brightsmile_appointments_page_html() {
	if ( ! current_user_can( 'brightsmile_view_appointments' ) ) {
		wp_die( esc_html__( 'You are not allowed to view this page.', 'brightsmile' ) );
	}
	?>
	<?php
	$appointments = new WP_Query(
		array(
			'post_type'      => 'appointment',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<table class="widefat striped">
	<thead>
		<tr>
			<th>Name</th>
			<th>Phone</th>
			<th>Treatment</th>
			<th>Preferred date</th>
		</tr>
	</thead>
	<tbody>
	<?php if ( $appointments->have_posts() ) : ?>
		<?php
		while ( $appointments->have_posts() ) :
			$appointments->the_post();
			$phone     = get_post_meta( get_the_ID(), 'appointment_phone', true );
			$treatment = get_post_meta( get_the_ID(), 'appointment_treatment', true );
			$date      = get_post_meta( get_the_ID(), 'appointment_date', true );
			?>
			<tr>
				<td><?php echo esc_html( get_the_title() ); ?></td>
				<td><?php echo esc_html( $phone ); ?></td>
				<td><?php echo esc_html( get_the_title( (int) $treatment ) ); ?></td>
				<td><?php echo esc_html( $date ); ?></td>
			</tr>
		<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<tr>
			<td colspan="4">No appointment requests yet.</td>
		</tr>
	<?php endif; ?>
	</tbody>
</table>
	</div>
	<?php
}

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
		'show_in_menu'  => false,
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
	$errors = array();
	$saved  = false;

	$values = array(
		'name'      => '',
		'phone'     => '',
		'treatment' => 0,
		'date'      => '',
	);

	if ( isset( $_POST['brightsmile_submit'] ) ) {
		$nonce = isset( $_POST['brightsmile_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['brightsmile_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'brightsmile_appointment' ) ) {
			$errors[] = 'Security check failed. Please reload the page and try again.';
		} else {
			$values['name']      = isset( $_POST['brightsmile_name'] ) ? sanitize_text_field( wp_unslash( $_POST['brightsmile_name'] ) ) : '';
			$values['phone']     = isset( $_POST['brightsmile_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['brightsmile_phone'] ) ) : '';
			$values['treatment'] = isset( $_POST['brightsmile_treatment'] ) ? absint( $_POST['brightsmile_treatment'] ) : 0;
			$values['date']      = isset( $_POST['brightsmile_date'] ) ? sanitize_text_field( wp_unslash( $_POST['brightsmile_date'] ) ) : '';

			if ( '' === $values['name'] ) {
				$errors[] = 'Please enter your name.';
			}

			if ( ! preg_match( '/^[0-9+\-\s]{7,15}$/', $values['phone'] ) ) {
				$errors[] = 'Please enter a valid phone number (7 to 15 digits).';
			}

			if ( 'service' !== get_post_type( $values['treatment'] ) ) {
				$errors[] = 'Please choose a treatment.';
			}

			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $values['date'] ) || strtotime( $values['date'] ) < strtotime( 'today' ) ) {
				$errors[] = 'Please choose a date that is today or later.';
			}

			if ( empty( $errors ) ) {
				$post_id = wp_insert_post(
					array(
						'post_type'   => 'appointment',
						'post_title'  => $values['name'],
						'post_status' => 'publish',
						'meta_input'  => array(
							'appointment_phone'     => $values['phone'],
							'appointment_treatment' => $values['treatment'],
							'appointment_date'      => $values['date'],
						),
					),
					true
				);

				if ( is_wp_error( $post_id ) ) {
					$errors[] = 'Sorry, we could not save your request. Please call the clinic.';
				} else {
					$saved = true;
				}
			}
		}
	}

	if ( $saved ) {
		return '<p>' . esc_html( 'Thank you! We have received your request and will call you soon.' ) . '</p>';
	}

	$services = get_posts(
		array(
			'post_type'      => 'service',
			'posts_per_page' => -1,
		)
	);

	ob_start();
	?>

	<?php if ( ! empty( $errors ) ) : ?>
		<ul>
			<?php foreach ( $errors as $error ) : ?>
				<li><?php echo esc_html( $error ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<form method="post">
		<?php wp_nonce_field( 'brightsmile_appointment', 'brightsmile_nonce' ); ?>

		<p>
			<label for="brightsmile_name">Name:</label>
			<input
				type="text"
				id="brightsmile_name"
				name="brightsmile_name"
				value="<?php echo esc_attr( $values['name'] ); ?>"
			>
		</p>

		<p>
			<label for="brightsmile_phone">Phone:</label>
			<input
				type="tel"
				id="brightsmile_phone"
				name="brightsmile_phone"
				value="<?php echo esc_attr( $values['phone'] ); ?>"
			>
		</p>

		<p>
			<label for="brightsmile_treatment">Treatment:</label>
			<select id="brightsmile_treatment" name="brightsmile_treatment">
				<option value="">Select a treatment</option>

				<?php foreach ( $services as $service ) : ?>
					<option
						value="<?php echo esc_attr( $service->ID ); ?>"
						<?php selected( $values['treatment'], $service->ID ); ?>
					>
						<?php echo esc_html( $service->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="brightsmile_date">Preferred date:</label>
			<input
				type="date"
				id="brightsmile_date"
				name="brightsmile_date"
				value="<?php echo esc_attr( $values['date'] ); ?>"
			>
		</p>

		<button type="submit" name="brightsmile_submit">
			Book appointment
		</button>
	</form>

	<?php
	return ob_get_clean();
}
add_shortcode( 'brightsmile_appointment_form', 'brightsmile_appointment_form_shortcode' );

/**
 * Get a dental tip from the external API.
 *
 * @return string
 */
function brightsmile_get_dental_tip() {
	$fallback = 'Brush your teeth twice a day for two minutes.';

	$cached_tip = get_transient( 'brightsmile_dental_tip' );

	if ( false !== $cached_tip ) {
		return $cached_tip;
	}

	$response = wp_remote_get(
		'https://jsonplaceholder.typicode.com/posts/1',
		array(
			'timeout' => 5,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $fallback;
	}

	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return $fallback;
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( ! is_array( $data ) || ! isset( $data['title'] ) ) {
		return $fallback;
	}

	$tip = sanitize_text_field( $data['title'] );

	set_transient( 'brightsmile_dental_tip', $tip, 12 * HOUR_IN_SECONDS );

	return $tip;
}

/**
 * Display the dental tip shortcode.
 *
 * @return string
 */
function brightsmile_dental_tip_shortcode() {
	$tip = brightsmile_get_dental_tip();

	ob_start();
	?>
	<div class="brightsmile-tip">
		<h3>Dental Tip</h3>
		<p><?php echo esc_html( $tip ); ?></p>
	</div>
	<?php

	return ob_get_clean();
}
add_shortcode( 'brightsmile_dental_tip', 'brightsmile_dental_tip_shortcode' );
