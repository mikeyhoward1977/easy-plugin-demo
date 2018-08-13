<?php
/**
 * Easy Plugin Demo API for creating Email template tags
 *
 * Email tags are wrapped in { }
 *
 * A few examples:
 *
 * {demo_name}
 * {demo_product_name}
 *
 *
 * To replace tags in content, use: epd_do_email_tags( $content, $blog_id, $user_id );
 *
 * To add tags, use: epd_add_email_tag( $tag, $description, $func ). Be sure to wrap epd_add_email_tag()
 * in a function hooked to the 'epd_add_email_tags' action
 *
 * @package     EPD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class EPD_Email_Template_Tags {

	/**
	 * Container for storing all tags
	 *
	 * @since	1.0
	 */
	private $tags;

	/**
	 * Blog ID
	 *
	 * @since	1.0
	 */
	private $blog_id;

	/**
	 * User ID
	 *
	 * @since	1.0
	 */
	private $user_id;

	/**
	 * Add an email tag
	 *
	 * @since	1.0
	 * @param	string		$tag	Email tag to be replaces in email
	 * @param	callable	$func	Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) ) {
			$this->tags[ $tag ] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	} // add

	/**
	 * Remove an email tag
	 *
	 * @since	1.0
	 * @param	string	$tag	Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[ $tag ] );
	} // remove

	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since	1.0
	 * @param	string	$tag	Email tag that will be searched
	 * @return	bool
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	} // email_tag_exists

	/**
	 * Returns a list of all email tags
	 *
	 * @since	1.0
	 * @return	array
	 */
	public function get_tags() {
		return $this->tags;
	} // get_tags

	/**
	 * Search content for email tags and filter email tags through their hooks.
	 *
	 * @since	1.0
	 * @param	string	$content	Content to search for email tags
	 * @param	int		$blog_id	The blog id
	 * @param	int		$user_id	The user id
	 * @return	string	Content with email tags filtered out.
	 */
	public function do_tags( $content, $blog_id, $user_id ) {

		// Check if there is at least one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->blog_id = $blog_id;
		$this->user_id = $user_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->blog_id = null;
		$this->user_id = null;

		return $new_content;
	} // do_tags

	/**
	 * Do a specific tag, this function should not be used. Please use epd_do_email_tags instead.
	 *
	 * @since	1.0
	 * @param	string	$m	Message
	 * @return	mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[ $tag ]['func'], $this->blog_id, $this->user_id, $tag );
	} // do_tag

} // EPD_Email_Template_Tags

/**
 * Add an email tag
 *
 * @since	1.0
 * @param	string		$tag	Email tag to be replace in email
 * @param	callable	$func	Hook to run when email tag is found
 */
function epd_add_email_tag( $tag, $description, $func ) {
	EPD()->email_tags->add( $tag, $description, $func );
} // epd_add_email_tag

/**
 * Remove an email tag
 *
 * @since	1.0
 * @param	string	$tag	Email tag to remove hook from
 */
function epd_remove_email_tag( $tag ) {
	EPD()->email_tags->remove( $tag );
} // epd_remove_email_tag

/**
 * Check if $tag is a registered email tag
 *
 * @since	1.0
 * @param	string	$tag	Email tag that will be searched
 * @return	bool
 */
function epd_email_tag_exists( $tag ) {
	return EPD()->email_tags->email_tag_exists( $tag );
} // epd_email_tag_exists

/**
 * Get all email tags
 *
 * @since	1.0
 * @return	array
 */
function epd_get_email_tags() {
	return EPD()->email_tags->get_tags();
} // epd_get_email_tags

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since	1.0
 * @return	string
 */
function epd_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = epd_get_email_tags();

    if ( empty( $email_tags ) || ! is_array( $email_tags ) )    {
        $email_tags = array();
    }

	if ( count( $email_tags ) > 0 ) {
		foreach ( $email_tags as $email_tag ) {
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';
		}
	}

	return $list;
} // epd_get_emails_tags_list

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @since	1.0
 * @param	string	$content	Content to search for email tags
 * @param	int		$blog_id	The blog id
 * @param	int		$user_id	The user id
 * @return	string	Content with email tags filtered out.
 */
function epd_do_email_tags( $content, $blog_id, $user_id ) {

	// Replace all tags
	$content = EPD()->email_tags->do_tags( $content, $blog_id, $user_id );

	// Return content
	return $content;
} // epd_do_email_tags

/**
 * Load email tags
 *
 * @since	1.0
 */
function epd_load_email_tags() {
	do_action( 'epd_add_email_tags' );
} // epd_load_email_tags
add_action( 'init', 'epd_load_email_tags', -999 );

/**
 * Add default EPD email template tags
 *
 * @since	1.0
 */
function epd_setup_email_tags() {

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'demo_fullname',
			'description' => __( 'The users full name, first and last', 'easy-plugin-demo' ),
			'function'    => 'epd_email_tag_demo_fullname'
		),
		array(
			'tag'         => 'demo_name',
			'description' => __( 'The users first name', 'easy-plugin-demo' ),
			'function'    => 'epd_email_tag_demo_name'
		),
		array(
			'tag'         => 'demo_product_name',
			'description' => __( 'The name of the product being demonstrated', 'easy-plugin-demo' ),
			'function'    => 'epd_email_tag_demo_product_name'
		),
		array(
			'tag'         => 'demo_site_expiration',
			'description' => __( 'The date and time the demo site will be deleted', 'easy-plugin-demo' ),
			'function'    => 'epd_email_tag_demo_site_expiration'
		),
		array(
			'tag'         => 'demo_site_password',
			'description' => __( 'A users password for their demo site', 'easy-plugin-demo' ),
			'function'    => 'epd_email_tag_demo_site_password'
		),
		array(
			'tag'         => 'demo_site_user_login',
			'description' => __( 'A users login for their demo site', 'easy-plugin-demo' ),
			'function'    => 'epd_email_tag_demo_site_user_login'
		),
		array(
			'tag'         => 'demo_site_url',
			'description' => __( 'The URL to a users demo site', 'easy-plugin-demo' ),
			'function'    => 'epd_email_tag_demo_site_url'
		)
	);

	// Apply epd_email_tags filter
	$email_tags = apply_filters( 'epd_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_tags as $email_tag ) {
		epd_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}

} // epd_setup_email_tags
add_action( 'epd_add_email_tags', 'epd_setup_email_tags' );

/**
 * Email template tag: demo_fullname
 * The users full name, first and last
 *
 * @since	1.0
 * @param	int		$blog_id
 * @param	int		$user_id
 * @return	string	User's first name
 */
function epd_email_tag_demo_fullname( $blog_id, $user_id ) {
	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return '';
	}

	return $user->first_name . ' ' . $user->last_name;
} // epd_email_tag_demo_fullname

/**
 * Email template tag: demo_name
 * The users first name
 *
 * @since	1.0
 * @param	int		$blog_id
 * @param	int		$user_id
 * @return	string	User's first name
 */
function epd_email_tag_demo_name( $blog_id, $user_id ) {
	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return '';
	}

	return $user->first_name;
} // epd_email_tag_demo_name

/**
 * Email template tag: demo_product_name
 * The name of the product being demonstrated
 *
 * @since	1.0
 * @param	int		$blog_id
 * @param	int		$user_id
 * @return	string	User's first name
 */
function epd_email_tag_demo_product_name( $blog_id, $user_id ) {
	$product = epd_get_option( 'product' );

	return esc_html( $product );
} // epd_email_tag_demo_product_name

/**
 * Email template tag: demo_site_expiration
 * The date after which the demo site will be deleted.
 *
 * @since	1.0
 * @param	int		$blog_id
 * @param	int		$user_id
 * @return	string	Date the site expires
 */
function epd_email_tag_demo_site_expiration( $blog_id, $user_id ) {
	return esc_html( epd_get_site_expiration_date( $blog_id ) );
} // epd_email_tag_demo_site_expiration

/**
 * Email template tag: demo_site_password
 * The users password
 *
 * @since	1.0
 * @param	int		$blog_id
 * @param	int		$user_id
 * @return	string	User's password
 */
function epd_email_tag_demo_site_password( $blog_id, $user_id ) {
	$password = get_user_option( 'epd_mu_pw', $user_id );

	return ! $password ? '' : $password;
} // epd_email_tag_demo_site_password

/**
 * Email template tag: demo_site_url
 * The site URL
 *
 * @since	1.0
 * @param	int		$blog_id
 * @param	int		$user_id
 * @return	string	Demo site URL
 */
function epd_email_tag_demo_site_url( $blog_id, $user_id ) {
	return get_blog_details( $blog_id )->siteurl;
} // epd_email_tag_demo_site_url

/**
 * Email template tag: demo_site_user_login
 * The users login
 *
 * @since	1.0
 * @param	int		$blog_id
 * @param	int		$user_id
 * @return	string	User's login
 */
function epd_email_tag_demo_site_user_login( $blog_id, $user_id ) {
	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return '';
	}

	return $user->user_login;
} // epd_email_tag_demo_site_user_login
