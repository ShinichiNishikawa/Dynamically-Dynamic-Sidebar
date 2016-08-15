<?php

/**
 * Register the admin page and the menu to it.
 *
 */
add_action( 'admin_menu', 'dds_add_theme_page' );
function dds_add_theme_page() {
	add_theme_page(
		__( 'Dynamically dinamic sidebar', 'dynamically-dynamic-sidebar' ),
		__( 'Dynamically dinamic sidebar', 'dynamically-dynamic-sidebar' ),
		'edit_theme_options',
		'dynamically-dynamic-sidebar',
		'dds_output_admin_panel'
	);
}

/**
 * Outputs the admin page.
 * Processes the posted data.
 *
 */
function dds_output_admin_panel() {

	// Anything $_POST ed
	if ( ! empty( $_POST ) ) {

		// Let's do check
		check_admin_referer( 'dynamically-dynamic-sidebar' );

		// Posted values.
		$posted_sidebars = $_POST["dds-widget-areas"];

		$posted_sidebars = array_filter( $posted_sidebars, "strlen" ); // Strip null, false, 0, and empty.
		$posted_sidebars = array_unique( $posted_sidebars ); // Strip the doubled ones.
		$posted_sidebars = array_values( $posted_sidebars ); // No jumps for the keys.
		$posted_sidebars = stripslashes_deep( $posted_sidebars ); // Handler for the quotes escaping slashes.

		$dds_sidebars = array();
		foreach ( $posted_sidebars as $ps ) {

			$key = sanitize_title( $ps );
			$val = esc_attr( $ps );
			$dds_sidebars[$key] = $val;

		}

		// Save the data.
		if ( isset( $dds_sidebars ) && is_array( $dds_sidebars ) ) {

			update_option( 'dds_sidebars', $dds_sidebars );

		}

		$posted_target = $_POST["dds_target_widget_area"];
		if ( $posted_target ) {

			update_option( 'dds_target_widget_area', $posted_target );

		} else {

			delete_option( 'dds_target_widget_area' );

		}

		$posted_widget_area_for_post_types = $_POST["dds_area_for_post_types"];
		if ( $posted_widget_area_for_post_types ) {

			update_option( 'dds_area_for_post_types', $posted_widget_area_for_post_types );

		} else {

			delete_option( 'dds_area_for_post_types' );

		}

	}

	$dds_sidebars = get_option( 'dds_sidebars' );
	$dds_target   = get_option( 'dds_target_widget_area' );
	$dds_a_f_pts  = get_option( 'dds_area_for_post_types' );


?>
<div class="wrap">
<h1><?php _e( 'Dynamic Widget Areas', 'dynamically-dynamic-sidebar' ); ?></h1>
<form action="" method="post">

<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><?php _e( 'Target Widget Area', 'dynamically-dynamic-sidebar' ); ?></th>
			<td>
				<label for="dds_target_widget_area">
					<select name="dds_target_widget_area" id="dds_target_widget_area">
						<option value=""><?php _e( 'Choose the target widget area to switch.', 'dynamically-dynamic-sidebar' ); ?></option>
						<?php
							global $wp_registered_sidebars;

							// Here's the list of the widget areas.
							$registered = $wp_registered_sidebars;
							// We want only the ones registered by themes and plugins. Not the user custom sidebar by this very plugin.
							foreach ( $dds_sidebars as $key => $val ) {
								unset( $registered[$key] );
							}

							foreach ( $registered as $key => $val ) {
								?>
								<option
									value="<?php echo esc_attr( $key ); ?>"
									<?php if ( $dds_target === $key ) { ?>
										selected="selected"
									<?php } ?>
								><?php echo esc_html( $val['name'] ); ?></option>
								<?php
							}

						?>
					</select>
				</label>
				<p class="description">Choose the widget area to switch with custom widget areas.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Custom Widget Areas', 'dynamically-dynamic-sidebar' ); ?></th>
			<td style="vertical-align: top;" width="25%">
				<?php _e( 'Add new', 'dynamically-dynamic-sidebar' ); ?> <label for="dds-new"><input id="dds-new" type="text" name="dds-widget-areas[]" value=""></label>
			</td>
			<td>
				<?php
				if ( $dds_sidebars ) {
					foreach ( $dds_sidebars as $key => $val ) {
						?>
						<input type="text" name="dds-widget-areas[]" value="<?php echo esc_attr( $val ); ?>"><br />
						<?php
					}
					?>
					<p class="description">Add and edit the name of custom widget areas.</p>
					<p class="description"><?php _e( 'Make the field blank to delete your areas.', 'dynamically-dynamic-sidebar' ); ?></p>
					<?php
				} else {
					_e( 'There\'s no dynamic widget areas yet.', 'dynamically-dynamic-sidebar' );
				}
				?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Post type Widget Areas', 'dynamically-dynamic-sidebar' ); ?></th>
			<td>
				<?php
					// https://developer.wordpress.org/reference/functions/get_post_types/
					$args = array(
						'public' => true,
					);
					$registered_post_types = get_post_types(
						$args,
						"object",
						"and"
					);
					unset( $registered_post_types["attachment"] );

					foreach ( $registered_post_types as $key => $val ) {
						?>
						<ul>
							<li>
								<label for="dds_area_for_post_types[<?php echo esc_attr( $key ); ?>]">
									<?php echo esc_attr( $val->label ); ?>
									<select name="dds_area_for_post_types[<?php echo esc_attr( $key ); ?>]" id="dds_area_for_post_types[<?php echo esc_attr( $key ); ?>]">
										<option value="dds-default"><?php _e( 'Default', 'dynamically-dynamic-sidebar' ); ?></option>
										<?php foreach ( $dds_sidebars as $dds_key => $dds_val ) {
											?>
											<option
												value="<?php echo esc_attr( $dds_key ); ?>"
												<?php
												if ( isset($dds_a_f_pts[$key]) && esc_attr( $dds_a_f_pts[$key] ) === $dds_key ) {
												?>
													selected="selected"
												<?php
												}
												?>


											><?php echo esc_html( $dds_val ); ?></option>
											<?php
										} ?>
									</select>
								</label>
							</li>
						</ul>
						<?php
					}

				?>
				<p class="description">Assign widget areas to your post types.</p>
			</td>
		</tr>
	</tbody>
</table>
<p class="submit"><input type="submit" id="submit" class="button button-primary" value="<?php _e( 'Save settings.', 'dynamically-dynamic-sidebar' ); ?>" /></p>
<?php wp_nonce_field( "dynamically-dynamic-sidebar" ); ?>
</form>
</div>
<?php
}
