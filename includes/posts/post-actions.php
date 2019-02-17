<?php
/**
 * Post and Page Actions
 *
 * @package     EPD
 * @subpackage  Posts/Action
 * @copyright   Copyright (c) 201, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Create required posts and pages for new blogs.
 *
 * @since	1.1
 * @param	int		$blog_id	The new blog ID
 * @param	array	$args		Array of arguments used whilst creating the blog
 * @return	void
 */
function epd_create_new_blog_posts_pages_action( $blog_id, $args )	{
	$post_types = epd_get_supported_post_types();

	foreach( $post_types as $post_type )	{
		epd_create_default_blog_posts( $blog_id, $post_type );
	}
} // epd_create_new_blog_posts_pages_action
add_action( 'epd_create_demo_site', 'epd_create_new_blog_posts_pages_action', 20, 2 );

/**
 * Hook into site creation to replicate post meta.
 *
 * @since	1.2.9
 * @param	int		$blog_id		The ID of the new blog
 * @param	int		$post_id		ID of the post created
 * @param	string	$post_type		The post type
 * @param	int		$old_post_id	ID of the old post
 */
function epd_create_replica_post_meta_action( $blog_id, $post_id, $post_type, $old_post_id )	{
	if ( epd_post_type_is_supported( $post_type ) )	{
		epd_create_replica_post_meta( $blog_id, $post_id, $old_post_id );
	}
} // epd_create_replica_post_meta_action
add_action( 'epd_create_default_blog_posts', 'epd_create_replica_post_meta_action', 10, 4 );
