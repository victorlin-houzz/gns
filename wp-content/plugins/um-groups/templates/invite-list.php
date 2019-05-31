<?php
/**
 * @Template: Invite List
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um">
	<?php $user_id = um_profile_id();

	do_action('um_groups_invites_directory_before', $arr_settings, $user_id );

	if ( ! empty( $user_id ) ) {

		$result = UM()->Groups()->api()->get_groups_invites_list( $user_id );

		if ( ! empty( $result ) ) { ?>

			<div class="um-groups-directory">

				<?php foreach ( $result as $group ) {

					$group_id = $group->ID;
					$has_joined = UM()->Groups()->api()->has_joined_group( $user_id, $group_id ); ?>

					<div class="um-group-item">

						<?php if ( $has_joined == 'pending_member_review' ) { ?>

							<div class="actions">
								<ul>
									<li>
										<a href="javascript:void(0);" data-group="<?php echo esc_attr( $group_id ) ?>"
										   data-user_id="<?php echo esc_attr( $user_id ) ?>"
										   class="um-button um-groups-ignore-invite-in-list um-alt um-right" >
											<?php _e( 'Ignore', 'um-groups' ) ?>
										</a>
									</li>
									<li>
										<a href="javascript:void(0);" data-group="<?php echo esc_attr( $group_id ) ?>"
										   data-user_id="<?php echo esc_attr( $user_id ) ?>"
										   class="um-button um-groups-confirm-invite-in-list um-right" >
											<?php _e( 'Confirm', 'um-groups' ) ?>
										</a>
									</li>
								</ul>
							</div>

						<?php } ?>

						<a href="<?php echo esc_attr( get_permalink( $group_id ) ) ?>">
							<?php if ( 'small' == $arr_settings['avatar_size'] ) {
								echo UM()->Groups()->api()->get_group_image( $group_id, 'default', 50, 50 );
							} else {
								echo UM()->Groups()->api()->get_group_image( $group_id, 'default', 100, 100 );
							} ?>

							<h4 class="um-group-name"><?php echo get_the_title( $group_id ) ?></h4>
						</a>

						<div class="um-group-meta">
							<ul>
								<li class="privacy">
									<?php echo um_groups_get_privacy_icon( $group_id );
									printf( __( '%s Group', 'um-groups' ), um_groups_get_privacy_title( $group_id ) ); ?>
								</li>
								<li class="description">
									<?php echo $group->post_content; ?>
								</li>
							</ul>
						</div>
						<div class="um-clear"></div>

					</div>

					<div class="um-clear"></div>

				<?php } ?>

			</div>

		<?php } else {
			_e( 'No invites found.', 'um-groups' );
		}
	} else {
		_e( 'No invites found.', 'um-groups' );
	} ?>
</div>