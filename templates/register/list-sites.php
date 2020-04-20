<?php
/**
 * This template part is used to display a users currently active demo sites 
 */
global $epd_register_redirect, $wp;

if ( is_user_logged_in() ) :

	$user_id    = get_current_user_id();
	$blog_id    = get_current_blog_id();
	$user_blogs = get_blogs_of_user( $user_id );

	if ( ! empty( $user_blogs ) ) :

		$strings = array(
			'intro'      => __( 'Your {demo_product_name} Demo Sites', 'easy-plugin-demo' ),
			'th_exp'     => __( 'Expires', 'easy-plugin-demo' ),
			'th_reg'     => __( 'Registered', 'easy-plugin-demo' ),
			'th_site'    => __( 'Site Name', 'easy-plugin-demo' ),
			'th_actions' => __( 'Actions', 'easy-plugin-demo' ),
		);

		$strings = apply_filters( 'epd_list_sites_template_strings', $strings );

		foreach( $strings as $key => $value )	{
			$strings[ $key ] = epd_do_email_tags( $value, $blog_id, $user_id );
		}

		extract( $strings ); ?>

		<h3><?php echo $intro; ?></h3>
		<table id="epd-sites-list" class="epd_sites">
			<thead>
				<th><?php echo $th_site; ?></th>
				<th><?php echo $th_reg; ?></th>
				<th><?php echo $th_exp; ?></th>
				<th><?php echo $th_actions; ?></th>
			</thead>

			<tbody>
				<?php foreach( $user_blogs as $user_blog ) :
					$date_format = 'Y/m/d ' . get_option( 'time_format' );
					$expires     = epd_get_site_expiration_date( $user_blog->userblog_id );
					$expires     = empty( $expires ) ? __( 'Never', 'easy-plugin-demo' ) : mysql2date( $date_format, $expires );
					$delete_url  = wp_nonce_url( add_query_arg( array(
						'epd_action' => 'delete_site',
						'site_id'    => $user_blog->userblog_id,
						), home_url( $wp->request )
						), 'delete_site', 'epd_nonce'
					);
					$actions    = array(
						sprintf(
							'<a href="%s">%s</a>',
							esc_url( $user_blog->siteurl ),
							__( 'Visit', 'easy-plugin-demo' )
						),
						sprintf(
							'<a href="%s">%s</a>',
							esc_url( get_admin_url( $user_blog->userblog_id ) ),
							__( 'Admin', 'easy-plugin-demo' )
						)
					);
					if ( ! is_main_site( $user_blog->userblog_id ) )	{
						$actions[] = sprintf(
							'<a href="%s">%s</a>',
							esc_url( $delete_url ),
							__( 'Delete', 'easy-plugin-demo' )
						);
					}
				?>
				<tr>
					<td><?php echo esc_html( $user_blog->blogname ); ?></td>
					<td><?php echo esc_html( epd_get_site_registered_time( $user_blog->userblog_id ) ); ?></td>
					<td><?php echo esc_html( $expires ); ?></td>
					<td><?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	<?php endif; ?>

<?php endif; ?>
