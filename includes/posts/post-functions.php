<?php
/**
 * Post and Page Functions
 *
 * @package     EPD
 * @subpackage  Posts/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve a list of supported post types.
 *
 * @since	1.2.9
 * @return	array	Array of supported post types
 */
function epd_get_supported_post_types()	{
	$post_types = array( 'post', 'page' );
	$post_types = apply_filters( 'epd_supported_post_types', $post_types );

	return $post_types;
} // epd_get_supported_post_types

/**
 * Whether or not a post type is supported.
 *
 * @since	1.2.9
 * @param	string	$post_type	Post type to check
 * @return	bool	True if supported, otherwise false
 */
function epd_post_type_is_supported( $post_type = 'post' )	{
	$post_types = epd_get_supported_post_types();
	$supported  = in_array( $post_type, $post_types );
	$supported  = apply_filters( 'epd_post_type_is_supported', $supported, $post_type );

	return (bool) $supported;
} // epd_post_type_is_supported

/**
 * Number of posts that can be auto created.
 *
 * @since	1.2.9
 * @return	int		Number of posts that can be created
 */
function epd_max_number_of_posts_to_create()	{
	$number = 3;
	$number = apply_filters( 'epd_max_number_of_posts_to_create', $number );

	return intval( $number );
} // epd_max_number_of_posts_to_create

/**
 * Retrieve a list of existing posts or pages from the primary blog.
 *
 * @since	1.1
 * @param	string|array	$post_type	The post type(s) to retrieve
 * @return	array			Array of post objects
 */
function epd_get_primary_blog_posts( $post_type = 'post' )	{
	$posts = array();

	switch_to_blog( get_network()->blog_id );

	if ( epd_post_type_is_supported( $post_type ) )	{

		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'orderby'        => 'title',
			'order'          => 'ASC'
		);

		$query = get_posts( $args );

		foreach( $query as $post )	{
			$posts[ $post->ID ] = esc_attr( get_the_title( $post->ID ) );
		}
	}

	restore_current_blog();

    $posts = apply_filters( 'epd_primary_blog_posts_' . $post_type, $posts );

	return $posts;
} // epd_get_primary_blog_posts

/**
 * Retrieve posts or pages that need creating on new blog.
 *
 * @since	1.1
 * @return	array	Array of post or page ID's to replicate
 */
function epd_posts_to_create( $type = 'post' )	{
	$option   = 'replicate_' . $type;
	$post_ids = epd_get_option( $option, array() );

	return $post_ids;
} // epd_posts_to_create

/**
 * Creates default posts and pages for a new blog.
 *
 * @since	1.1
 * @param	int		$blog_id	The ID of the new blog
 * @param	string	$post_type	'post' or 'page'
 * @return	int		The number of posts created
 */
function epd_create_default_blog_posts( $blog_id, $post_type = 'post' )	{
	$done = 0;

	if ( ! epd_post_type_is_supported( $post_type ) )	{
		return $done;
	}

	$post_ids  = epd_posts_to_create( $post_type );
    $new_posts = array();

    if ( empty( $post_ids ) )   {
        return $done;
    }

    switch_to_blog( get_network()->blog_id );
	$old_posts = get_posts( array(
		'posts_per_page' => epd_max_number_of_posts_to_create(),
		'include'        => $post_ids,
		'post_type'      => $post_type,
		'post_status'    => 'any'
	) );
	restore_current_blog();
	switch_to_blog( $blog_id );

	do_action( 'epd_before_create_default_blog_posts', $blog_id, $post_type, $post_ids );

	if ( $old_posts )	{
		foreach( $old_posts as $old_post )	{
			$args = array(
				'comment_status' => $old_post->comment_status,
				'ping_status'    => $old_post->ping_status,
				'post_author'    => get_current_user_id(),
				'post_content'   => $old_post->post_content,
				'post_excerpt'   => $old_post->post_excerpt,
				'post_name'      => $old_post->post_name,
				'post_parent'    => $old_post->post_parent,
				'post_password'  => $old_post->post_password,
				'post_status'    => $old_post->post_status,
				'post_title'     => $old_post->post_title,
				'post_type'      => $old_post->post_type,
				'to_ping'        => $old_post->to_ping,
				'menu_order'     => $old_post->menu_order
			);

			do_action( 'epd_before_creating_default_blog_post', $blog_id, $post_type, $old_post->ID );
			$new_post_id = wp_insert_post( $args, true );
			if ( ! is_wp_error( $new_post_id ) )	{
				$done++;
			} else	{
				error_log( $new_post_id->get_error_message() );
			}
			do_action( 'epd_create_default_blog_post', $blog_id, $new_post_id, $post_type, $old_post->ID );
		}
	}

	restore_current_blog();
    do_action( 'epd_create_default_blog_posts', $blog_id, $post_type, $new_posts, $old_posts );

	return $done;
} // epd_create_default_blog_posts

/**
 * Replicate a posts meta data.
 *
 * @since	1.2.9
 * @param	int		$blog_id		The new site ID
 * @param	int		$post_id		The new post ID
 * @param	int		$old_post_id	The old post ID
 * @return	void
 */
function epd_create_replica_post_meta( $blog_id, $post_id, $old_post_id )	{

	$switched = false;

	switch_to_blog( get_network()->blog_id );
	$meta = get_post_custom( $old_post_id );
	restore_current_blog();

	if ( $blog_id != get_current_blog_id() )	{
		switch_to_blog( $blog_id );
		$switched = true;
	}

	do_action( 'epd_before_create_replica_post_meta', $post_id, $meta, $blog_id );

	foreach ( $meta as $key => $values )	{
		foreach ( $values as $value )	{

			do_action( 'epd_before_create_replica_post_meta_key', $post_id, $key, $value, $blog_id );

			if ( '_wp_old_slug' == $key )	{
				continue;
			}

			$meta_add = add_post_meta( $post_id, $key, $value );

			do_action( 'epd_create_replica_post_meta_key', $post_id, $key, $value, $meta_add, $blog_id );
		}
	}

	do_action( 'epd_create_replica_post_meta', $post_id, $meta, $blog_id );

	if ( $switched )	{
		restore_current_blog();
	}

} // epd_create_replica_post_meta
