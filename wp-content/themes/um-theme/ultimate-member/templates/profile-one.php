<?php /* Template: Profile 2 */ ?>
<div class="um um-profile-one-container <?php echo $this->get_class( $mode ); ?> um-<?php echo esc_attr( $form_id ); ?> um-role-<?php echo um_user( 'role' ); ?> ">
<div class="um-form">
		<?php do_action( 'um_profile_before_header', $args );?>
		<?php if ( um_is_on_edit_profile() ) : ?>
			<form method="post" action="">
		<?php endif;?>

		<div class="boot-row">
			<div class="boot-col-md-2 um-profile-one">

				<?php
					// Apply Ultimate Member Profile Layout Customizations.
					if ( $args['template'] == 'profile-one' ) {

					    add_action( 'um_profile_layout_two', 'um_theme_below_profile_layout_two_image_open', 10 );
					    add_action( 'um_profile_layout_two', 'um_theme_below_profile_layout_two_image_close', 30 );
					    remove_action( 'um_profile_header', 'um_profile_header', 9 );
					    remove_action( 'um_profile_menu', 'um_profile_menu', 9 );

					   	// Check if UM Followers extension is active.
					    if ( function_exists( 'um_followers_check_dependencies' ) ) {
					        remove_action( 'um_profile_navbar', 'um_followers_add_profile_bar', 4 );
					        add_action( 'um_profile_layout_one', 'um_followers_add_profile_bar', 20 );
					    }

					    // Check if UM Friends extensions is active.
					    if ( function_exists( 'um_friends_plugins_loaded' ) ) {
		                	remove_action( 'um_before_profile_main_meta', 'um_friends_add_button' );
		                	remove_action('um_after_profile_header_name_args', 'um_friends_add_button_nocover', 90, 1 );
					        add_action( 'um_profile_layout_one', 'um_theme_friend_box_profile', 23 );
					        add_action( 'um_profile_layout_one', 'um_theme_friends_add_button', 21 );
					    }

					    // Check if Private Messages extension is active.
					    if ( function_exists( 'um_messaging_plugins_loaded' ) ) {
					        remove_action( 'um_profile_navbar', array( UM()->Messaging_API()->profile(), 'add_profile_bar' ), 5 );
					        add_action( 'um_profile_layout_one', array( UM()->Messaging_API()->profile(), 'add_profile_bar' ), 22 );
					   	}
					}
				?>

				<?php
					do_action( 'um_profile_header', $args );

					$default_size = str_replace( 'px', '', $args['photosize'] );

					$overlay = '<span class="um-profile-photo-overlay"><span class="um-profile-photo-overlay-s"><ins><i class="um-faicon-camera"></i></ins></span></span>';
				?>

				<?php do_action( 'um_pre_header_editprofile', $args ); ?>

				<div class="um-profile-photo" data-user_id="<?php echo um_profile_id(); ?>">

					<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-profile-photo-img" title="<?php echo esc_attr( um_user( 'display_name' ) ); ?>">
					   <?php echo $overlay . get_avatar( um_user( 'ID' ), $default_size ); ?>
					</a>

					<?php
						if ( ! isset( UM()->user()->cannot_edit ) ) {

							UM()->fields()->add_hidden_field( 'profile_photo' );

							if ( ! um_profile( 'profile_photo' ) ) { // has profile photo

								$items = array(
									'<a href="#" class="um-manual-trigger" data-parent=".um-profile-photo" data-child=".um-btn-auto-width">' . __( 'Upload photo', 'um-theme' ) . '</a>',
									'<a href="#" class="um-dropdown-hide">' . __( 'Cancel', 'um-theme' ) . '</a>',
								);

								$items = apply_filters( 'um_user_photo_menu_view', $items );

								echo UM()->profile()->new_ui( 'bc', 'div.um-profile-photo', 'click', $items );

							} elseif ( UM()->fields()->editing == true ) {

								$items = array(
									'<a href="#" class="um-manual-trigger" data-parent=".um-profile-photo" data-child=".um-btn-auto-width">' . __( 'Change photo', 'um-theme' ) . '</a>',
									'<a href="#" class="um-reset-profile-photo" data-user_id="' . um_profile_id() . '" data-default_src="' . um_get_default_avatar_uri() . '">' . __( 'Remove photo', 'um-theme' ) . '</a>',
									'<a href="#" class="um-dropdown-hide">' . __( 'Cancel', 'um-theme' ) . '</a>',
								);

								$items = apply_filters( 'um_user_photo_menu_edit', $items );

								echo UM()->profile()->new_ui( 'bc', 'div.um-profile-photo', 'click', $items );
							}
						}
					?>
				</div>

				<div class="um-profile-meta boot-d-block boot-d-sm-none">

		            <?php do_action( 'um_before_profile_main_meta', $args ); ?>

					<div class="um-main-meta">

						<?php if ( $args['show_name'] ) { ?>
							<div class="um-name">

								<a href="<?php echo esc_url( um_user_profile_url() );?>" title="<?php echo esc_attr( um_user( 'display_name' ) ); ?>">
									<?php echo um_user( 'display_name', 'html' ); ?>
								</a>

								<?php do_action( 'um_after_profile_name_inline', $args ); ?>

							</div>
						<?php } ?>

						<div class="um-clear"></div>

						<?php do_action( 'um_after_profile_header_name_args', $args ); ?>

						<?php do_action( 'um_after_profile_header_name' ); ?>

					</div>

					<?php if ( isset( $args['metafields'] ) && ! empty( $args['metafields'] ) ) { ?>
						<div class="um-meta">
							<?php echo UM()->profile()->show_meta( $args['metafields'] ); ?>
						</div>
					<?php } ?>

					<?php do_action( 'um_after_header_meta', um_user( 'ID' ), $args ); ?>

				</div>


				<?php do_action( 'um_profile_layout_one' ); ?>
			</div>

			<div class="boot-col-md-10 um-profile-one-content">

				<?php um_fetch_user( um_get_requested_user() );?>

				<div class="um-profile-meta boot-d-none boot-d-sm-block">

		            <?php do_action( 'um_before_profile_main_meta', $args ); ?>

					<div class="um-main-meta">

						<?php if ( $args['show_name'] ) { ?>
							<div class="um-name">

								<a href="<?php echo um_user_profile_url(); ?>" title="<?php echo um_user( 'display_name' ); ?>">
									<?php echo um_user( 'display_name', 'html' ); ?>
								</a>

								<?php do_action( 'um_after_profile_name_inline', $args ); ?>

							</div>
						<?php } ?>

						<div class="um-clear"></div>

						<?php do_action( 'um_after_profile_header_name_args', $args ); ?>

						<?php do_action( 'um_after_profile_header_name' ); ?>

					</div>

					<?php if ( isset( $args['metafields'] ) && ! empty( $args['metafields'] ) ) { ?>
						<div class="um-meta">
							<?php echo UM()->profile()->show_meta( $args['metafields'] ); ?>
						</div>
					<?php } ?>

					<?php do_action( 'um_after_header_meta', um_user( 'ID' ), $args ); ?>

				</div>



		<?php
			if ( ! UM()->options()->get( 'profile_menu' ) ) {
				return;
			}

			// get active tabs
			$tabs = UM()->profile()->tabs_active();
			$tabs = apply_filters( 'um_user_profile_tabs', $tabs );

			UM()->user()->tabs = $tabs;

			// need enough tabs to continue
			if ( count( $tabs ) <= 1 ) {
				return;
			}

			$active_tab = UM()->profile()->active_tab();

			if ( ! isset( $tabs[ $active_tab ] ) ) {
				$active_tab = 'main';
				UM()->profile()->active_tab = $active_tab;
				UM()->profile()->active_subnav = null;
			}

			// Move default tab priority
			$default_tab = UM()->options()->get( 'profile_menu_default_tab' );
			$dtab = ( isset( $tabs[ $default_tab ] ) ) ? $tabs[ $default_tab ] : 'main';
			if ( isset( $tabs[ $default_tab] ) ) {
				unset( $tabs[ $default_tab ] );
				$dtabs[ $default_tab ] = $dtab;
				$tabs = $dtabs + $tabs;
			} ?>

			<div class="um-profile-nav">

				<?php foreach ( $tabs as $id => $tab ) {

					if ( isset( $tab['hidden'] ) ) {
						continue;
					}

					$nav_link = UM()->permalinks()->get_current_url( get_option( 'permalink_structure' ) );
					$nav_link = remove_query_arg( 'um_action', $nav_link );
					$nav_link = remove_query_arg( 'subnav', $nav_link );
					$nav_link = add_query_arg( 'profiletab', $id, $nav_link );
					$nav_link = apply_filters( "um_profile_menu_link_{$id}", $nav_link );

					$profile_nav_class = '';
					$profile_nav_class .= ' without-icon';

					if ( $id == $active_tab ) {
						$profile_nav_class .= ' active';
					} ?>

					<div class="um-profile-nav-item um-profile-nav-<?php echo $id . ' ' . $profile_nav_class; ?>">

							<a href="<?php echo $nav_link; ?>" class="uimob800-show uimob500-show uimob340-show um-tip-n"
							   title="<?php echo esc_attr( $tab['name'] ); ?>" original-title="<?php echo esc_attr( $tab['name'] ); ?>">

								<?php if ( isset( $tab['notifier'] ) && $tab['notifier'] > 0 ) { ?>
									<span class="um-tab-notifier uimob800-show uimob500-show uimob340-show"><?php echo $tab['notifier']; ?></span>
								<?php } ?>
							</a>
							<a href="<?php echo $nav_link; ?>" class="uimob800-hide uimob500-hide uimob340-hide"
							   title="<?php echo esc_attr( $tab['name'] ); ?>">

								<?php if ( isset( $tab['notifier'] ) && $tab['notifier'] > 0 ) { ?>
									<span class="um-tab-notifier"><?php echo $tab['notifier']; ?></span>
								<?php } ?>

								<span class="title"><?php echo $tab['name']; ?></span>
							</a>
					</div>

				<?php } ?>

			</div>

					<?php foreach ( $tabs as $id => $tab ) {

						if ( isset( $tab['subnav'] ) && $active_tab == $id ) {

							$active_subnav = ( UM()->profile()->active_subnav() ) ? UM()->profile()->active_subnav() : $tab['subnav_default']; ?>

							<div class="um-profile-subnav">
								<?php foreach ( $tab['subnav'] as $id_s => $subtab ) { ?>

									<a href="<?php echo esc_url( add_query_arg( 'subnav', $id_s ) ); ?>" class="<?php if ( $active_subnav == $id_s ) echo 'active'; ?>">
										<?php echo $subtab; ?>
									</a>

								<?php } ?>
							</div>
						<?php }
					} ?>


						<?php do_action( 'um_profile_layout_one_below_meta' ); ?>

						<?php

							$classes = apply_filters( 'um_profile_navbar_classes', '' ); ?>

							<div class="um-profile-navbar <?php echo $classes ?>">
								<?php do_action( 'um_profile_navbar', $args ); ?>
							</div>

							<?php do_action( 'um_profile_menu', $args );
						?>


							<?php

								$nav 	= UM()->profile()->active_tab;
								$subnav = ( get_query_var( 'subnav' ) ) ? get_query_var( 'subnav' ) : 'default';

								echo "<div class='um-profile-body $nav $nav-$subnav'>";

								do_action( "um_profile_content_{$nav}", $args );

								do_action( "um_profile_content_{$nav}_{$subnav}", $args );

								echo '</div>';
							?>
					<?php do_action( 'um_profile_menu_after' );?>

			</div>
		</div>

		<?php
			if ( um_is_on_edit_profile() ) : ?>
			</form>
		<?php endif;?>
</div>
</div>
