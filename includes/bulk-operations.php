<?php
/**
 * Created by PhpStorm.
 * User: garykovar
 * Date: 11/21/16
 * Time: 10:51 PM
 */

/**
 * Admin-ajax kickoff the bulk processing.
 *
 * @author Gary Kovar
 *
 * @since  1.2.0
 */
function wds_queue_bulk_processing() {
	$type_array = array( 'post', 'page' );
	if ( ! in_array( $_POST['posttype'], $type_array ) ) {
		return;
	}
	wp_schedule_single_event( time() + 60, 'wds_bulk_process_video_query_init', array( $_POST['posttype'] ) );
}

/**
 * Process the scheduled post-type.
 *
 * If there are more to do when it's done...do it.
 *
 * @author Gary Kovar
 *
 * @since  1.2.0
 */
function wds_bulk_process_video_query( $post_type ) {
	// Get a list of IDs to process.
	$args  = array(
		'post_type'      => $post_type,
		'meta_query'     => array(
			array(
				'meta_key'     => '_is_video',
				'meta_compare' => 'NOT EXISTS',
			),
		),
		'posts_per_page' => 10,
		'fields'         => 'ids',
	);
	$query = new WP_Query( $args );

	// Process these jokers.
	foreach ( $query->posts as $post_id ) {
		wds_check_if_content_contains_video( $post_id, get_post( $post_id ) );
	}

	$reschedule_task = new WP_Query( $args );
	if ( $reschedule_task->post_count > 1 ) {
		wp_schedule_single_event( time() + ( 60 * 10 ), 'wds_bulk_process_video_query_init', array( $post_type ) );
	}
}