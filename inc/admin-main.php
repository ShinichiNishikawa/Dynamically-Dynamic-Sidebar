<?php

// ページとメニューの登録
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

// 管理画面の出力
function dds_output_admin_panel() {

	// post された
	if ( ! empty( $_POST ) ) {

		// リファラをチェック
		check_admin_referer( 'dynamically-dynamic-sidebar' );

		// ポストされてきたもの
		$posted_sidebars = $_POST["dds-widget-areas"];

		$posted_sidebars = array_filter( $posted_sidebars, "strlen" ); // から削除
		$posted_sidebars = array_unique( $posted_sidebars ); // 重複排除
		$posted_sidebars = array_values( $posted_sidebars ); // キーがジャンプするのを防ぐ
		$posted_sidebars = stripslashes_deep( $posted_sidebars ); // スラッシュ問題の解決

		$dds_sidebars = array();
		foreach ( $posted_sidebars as $ps ) {
			$key = sanitize_title( $ps );
			$val = esc_attr( $ps );
			$dds_sidebars[$key] = $val;
		}

		// 保存
		if ( isset( $dds_sidebars ) && is_array( $dds_sidebars ) ) {
			update_option( 'dds_sidebars', $dds_sidebars );
		}

		$posted_target = $_POST["dds_target_widget_area"];
		if ( $posted_target ) {
			update_option( 'dds_target_widget_area', $posted_target );
		} else {
			delete_option( 'dds_target_widget_area' );
		}

	}

	$dds_sidebars = get_option( 'dds_sidebars' );
	$dds_target   = get_option( 'dds_target_widget_area' );
?>
<div class="wrap">
<h1><?php _e( 'Dynamic Widget Areas', 'dynamically-dynamic-sidebar' ); ?></h1>
<form action="" method="post">

<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><?php _e( 'Target widget area to switch', 'dynamically-dynamic-sidebar' ); ?></th>
			<td>
				<label for="dds_target_widget_area">
					<select name="dds_target_widget_area" id="dds_target_widget_area">
						<option value=""><?php _e( 'Choose the target widget area to switch.', 'dynamically-dynamic-sidebar' ); ?></option>
						<?php
							global $wp_registered_sidebars;

							// here's the list of the widget areas.
							$registered = $wp_registered_sidebars;
							// we want only the ones registered by other theme(plugins)l
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
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Manage Widget Areas', 'dynamically-dynamic-sidebar' ); ?></th>
			<td>
				<?php
				if ( $dds_sidebars ) {
					foreach ( $dds_sidebars as $key => $val ) {
						?>
						<input type="text" name="dds-widget-areas[]" value="<?php echo esc_attr( $val ); ?>"><br />
						<?php
					}
					?>
					<p class="description"><?php _e( 'Make the field blank to delete your areas.', 'dynamically-dynamic-sidebar' ); ?></p>
					<?php
				} else {
					_e( 'There\'s no dynamic widget areas yet.', 'dynamically-dynamic-sidebar' );
				}
				?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Add new', 'dynamically-dynamic-sidebar' ); ?></th>
			<td>
				<label for="dds-new"><?php _e( 'Add New', 'dynamically-dynamic-sidebar' ); ?> <input id="dds-new" type="text" name="dds-widget-areas[]" value=""></label>
			</td>
		</tr>
	</tbody>
</table>
<p class="submit"><input type="submit" id="submit" class="button button-primary" value="<?php _e( 'Save dynamically dynamic widget settings.', 'dynamically-dynamic-sidebar' ); ?>" /></p>
<?php wp_nonce_field( "dynamically-dynamic-sidebar" ); ?>
</form>
</div>
<?php
}
