<?php
/**
 * EPDP Reset Site Class
 *
 * @package		EPDP Site
 * @subpackage	Site
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * EPD_Reset_Site Class
 *
 * @since	1.3
 */
class EPD_Reset_Site {

	/**
	 * The site id
	 *
	 * @since	1.3
	 * @var		int
	 */
	public $site_id;

	/**
	 * The WP_Site site
	 *
	 * @since	1.3
	 * @var		object
	 */
	public $site;

	/**
	 * The blog (network) id
	 *
	 * @since	1.3
	 * @var		int
	 */
	public $blog_id;

	/**
	 * Site domain
	 *
	 * @since	1.3
	 * @var		string
	 */
	public $domain;

	/**
	 * Site path
	 *
	 * @since	1.3
	 * @var		string
	 */
	public $path;

	/**
	 * The new site id
	 *
	 * @since	1.3
	 * @var		int
	 */
	public $new_site_id = 0;

	/**
	 * The site's primary user ID
	 *
	 * @since	1.3
	 * @var		int
	 */
	private $user_id;

	/**
	 * The site's registered date
	 *
	 * @since	1.3
	 * @var		string
	 */
	private $registered;

	/**
	 * The site's expiration timestamp
	 *
	 * @since	1.3
	 * @var		string
	 */
	private $expires;

	/**
	 * The site's lifetime in seconds
	 *
	 * @since	1.3
	 * @var		string
	 */
	private $lifetime;

	/**
	 * The site's meta
	 *
	 * @since	1.3
	 * @var		array
	 */
	private $meta = array();

	/**
	 * Get things going
	 *
	 * @since	1.3
	 */
	public function __construct( $_id = false ) {
		if ( $this->setup_site( $_id ) )  {
            $this->init();
        }
	} // __construct

	/**
	 * Given the site data, let's set the variables
	 *
	 * @since	1.3
	 * @param 	object	$site	The site post object
	 * @return	bool			If the setup was successful or not
	 */
	private function setup_site( $site_id ) {
        $this->site_id    = $site_id;
		$this->site       = get_site( $this->site_id );
		$this->blog_id    = $this->site->site_id;
		$this->domain     = $this->site->domain;
		$this->path       = $this->site->path;
		$this->user_id    = epd_get_site_primary_user_id( $this->site_id );
		$this->registered = $this->site->registered;
		$this->expires    = epd_get_site_expiration_timestamp( $this->site_id );
		$this->lifetime   = epd_get_site_lifetime( $this->site_id );
		$this->meta       = $this->define_site_meta();

		return true;
	} // setup_site

	/**
     * Initialise hooks
     *
     * @since   1.3
     */
    public function init()  {
		// Prevent primary user deletion
        add_filter( 'epd_delete_site_delete_users', array( $this, 'protect_primary_user' ), 100 );

		// Prevent site count incrementing during reset
		add_filter( 'epd_increment_site_count', '__return_false' );

		// Restore site meta
		add_filter( 'epd_default_blog_meta', array( $this, 'set_site_meta' ), 999 );
    } // init

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since	1.2
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			return new WP_Error(
				'epd-site-invalid-property', 
				printf( __( "Can't get property %s", 'easy-plugin-demo' ), $key )
			);
		}
	} // __get

	/**
	 * Define the site meta we need to restore.
	 *
	 * @since	1.3
	 * @return	array	Array of site meta to restore
	 */
	public function define_site_meta()	{
		$restore_meta = array(
			'epd_site_expires'  => $this->expires,
			'epd_site_lifetime' => $this->lifetime
		);

		$restore_meta = apply_filters( 'epd_define_site_meta', $restore_meta, $this->site_id );

		return $restore_meta;
	} // define_site_meta

	/**
	 * Protect the primary site user from deletion.
	 *
	 * @since	1.2
	 * @param	array	$users	Users to be deleted with site
	 * @return	array	Users to be deleted with site
	 */
	public function protect_primary_user( $users )	{
		foreach( $users as $key => $user )	{
			if ( $this->user_id == $user )	{
				unset( $users[ $key ] );
			}
		}

		return $users;
	} // protect_primary_user

	/**
	 * Set the site meta.
	 *
	 * @since	1.3
	 * @param	array	$meta	Array of site meta
	 * @return	array	Array of site meta
	 */
	public function set_site_meta( $meta )	{
		foreach( $this->meta as $meta_key => $meta_value )	{
			$meta[ $meta_key ] = $meta_value;
		}

		return $meta;
	} // set_site_meta

	/**
	 * Set the site args.
	 *
	 * @since	1.3
	 * @return	array	Array of args to parse to epd_create_demo_site()
	 */
	public function get_site_args()	{
		$args = array(
			'domain'     => $this->domain,
			'path'       => $this->path,
			'user_id'    => $this->user_id,
			'meta'       => array(),
			'network_id' => $this->blog_id
		);

		$args = apply_filters( 'epd_reset_site_args', $args, $this );

		return $args;
	} // get_site_args

    /**
     * Update the blog table for the new site.
     *
     * @since   1.3
     * @return  bool
     */
    public function update_blog_table()   {
        $args = array(
            'registered' => $this->registered
        );

        $args = apply_filters( 'epd_update_blog_table_args', $args );

        if ( ! empty( $args ) ) {
            return update_blog_details( $this->new_site_id, $args );
        }

        return true;
    } // update_blog_table

	/**
	 * Execute the reset.
	 *
	 * @since	1.3
	 * @return	bool	True if successful, false if the site was not reset
	 */
	public function execute()	{
		switch_to_blog( get_network()->blog_id );

		if ( epd_delete_site( $this->site_id ) )	{
			$args = $this->get_site_args();
			$this->new_site_id = epd_create_demo_site( $args );
		}

		if ( ! empty( $this->new_site_id ) )	{
            $this->update_blog_table();
			epd_redirect_after_register( $this->new_site_id, $this->user_id );
		}
	} // execute

} // EPD_Reset_Site
