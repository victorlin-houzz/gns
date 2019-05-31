<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( $args['found_members'] > 0 ) {

	foreach ( $args['members'] as $member ) { ?>

		<div class="um-groups-user-wrap" data-group-uid="<?php echo esc_attr( $member['user']['id'] ) ?>"
		     data-group-id="<?php echo esc_attr( $member['group_id'] ) ?>">

			<div class="user-details">
				<div style="float:left; margin-right:20px;">
					<a href="<?php echo esc_attr( $member['user']['url'] ) ?>">
						<?php echo $member['user']['avatar'] ?>
					</a>
				</div>

				<?php if ( ! isset( $args['menus'] ) ) {
					$args['menus'] = array();
				}

				$menus = apply_filters( "um_groups_list_users_menu__{$args['load_more']}", $args['menus'], $member['user']['id'], $args['group_id'], $member['user']['has_joined'], $args, $member );
				if ( ! empty( $menus ) ) { ?>
					<div id="um-group-buttons" class="um-group-buttons">

						<?php if ( count( $menus ) <= 1 ) {
							$first_menu = key( $menus );

							if ( $first_menu ) { ?>
								<a href="javascript:void(0);" class="um-group-button"
								   data-action-key="<?php echo esc_attr( $first_menu ) ?>">
									<?php echo $menus[ $first_menu ] ?>
								</a>
								<?php unset( $menus[ $first_menu ] );
							}
						}

						if ( ! empty( $menus ) ) { ?>
							<a href="javascript:void(0);" class="um-group-button um-group-button-more">
								<i class="um-faicon-ellipsis-h"></i>
							</a>
							<div class="um-groups-wrap-buttons">
								<ul class="um-group-buttons-more">
									<?php foreach ( $menus as $menu_key => $menu_title ) { ?>
										<li>
											<a href="javascript:void(0);" data-action-key="<?php echo esc_attr( $menu_key ) ?>">
												<?php echo $menu_title ?>
											</a>
										</li>
									<?php } ?>
								</ul>
							</div>
						<?php } ?>
					</div>
				<?php } ?>

				<div>
					<div>
						<a href="<?php echo esc_attr( $member['user']['url'] ) ?>">
							<?php echo $member['user']['name']; ?>
						</a>
					</div>
					<div>
						<ul style="list-style:none;">
							<?php if ( isset( $member['user']['joined'] )  && ! empty( $member['user']['joined'] ) ) { ?>
								<li><?php printf( __( 'Joined %s', 'um-groups' ), $member['user']['joined'] ) ?></li>
							<?php }
							do_action("um_groups_users_list_after_details", $member['user']['id'], $args['group_id'], $menus, $member['user']['has_joined'] );
							do_action("um_groups_users_list_after_details__{$args['load_more']}", $member['user']['id'], $args['group_id'], $member, $menus, $member['user']['has_joined'] ); ?>
						</ul>
					</div>
				</div>
			</div>
		</div>

	<?php }

}

$limit = apply_filters( 'um_groups_users_per_page', 0 );

if ( $args['found_members'] >= $limit ) { ?>
	<div style="text-align:center;clear:both;">
		<a href="javascript:void(0);" class="um-groups-load-more" data-group-id="<?php echo esc_attr( $args['group_id'] ) ?>"
		   data-load-more="<?php echo esc_attr( $args['load_more'] ) ?>" data-users-offset="<?php echo esc_attr( $args['offset'] ) ?>">
			<?php _e('Load more...', 'um-groups' ) ?>
		</a>
	</div>
<?php }

if ( $args['found_members'] <= 0 && $args['offset'] == 10 && $args['doing_search'] == true ) {
	_e("Sorry, we couldn't find any results for this search.","um-groups");
}

if ( $args['found_members'] <= 0 && $args['offset'] == 10 && $args['doing_search'] == false ) {
	_e("There are no members to show.","um-groups");
}