<?php

function dds_get_taxonomies() {

	// コンテンツの分類に使うタクソノミに限定
	$args = array(
		'public'  => true,
		'show_ui' => true,
	);

	$taxonomies = get_taxonomies( $args );

	// カテゴリを最後にしたい。他のものから上書きしそうだから。
	$reversed   = array_reverse( $taxonomies );

	return $reversed;

}

// return: widget_id, widget_name and term object detemining the widget id.
// Returns the FIRST FOUND widget area.
function dds_get_widget_of_post_by_term( $post ) {

	// マスター
	$terms  = array();
	$return = array();

	// 登録されているタクソノミ
	$taxonomies = dds_get_taxonomies();

	// ユーザ作成のエリア
	$allocatable_widgets  = get_option( 'dds_sidebars' );

	// 各タクソノミのタームを取得
	foreach ( $taxonomies as $taxonomy ) {

		$temp = get_the_terms( $post, $taxonomy ); // タームを取得

		// タームに属していればマスターに入れる
		if ( is_array( $temp ) ) {
			$terms = array_merge( $terms, $temp );
		}

	}

	// タームを点検
	foreach ( $terms as $t ) {

		// そのタームに割当てられたウィジェットエリアを取得
		$area_id   = get_term_meta( $t->term_id, 'dds_widget_area', true );

		// もしあれば、配列を作って、ループを抜ける
		if ( $area_id ) {

			$return["area-id"]   = $area_id;
			$return["area-name"] = $allocatable_widgets[$area_id];
			$return["term"]      = $t;

			break;

		}

	}

	if ( $return ) {
		return $return;
	} else {
		return false;
	}

}

function dds_get_widget_name_by_id( $w_id ) {

	if ( !$w_id ) {
		return false;
	}

	$user_define_widgets  = get_option( 'dds_sidebars' );
	if ( array_key_exists( $w_id, $user_define_widgets ) ) {
		return $user_define_widgets[$w_id];
	}

	return false;

}
