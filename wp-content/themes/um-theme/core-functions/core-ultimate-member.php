<?php
/**
 * Enable support for Ultimate Member.
 *
 * @link   https://wordpress.org/plugins/ultimate-member/
 *
 * @since  0.50
 */

/**
 * Ultimate Member Plugin
 */
add_action( 'customize_controls_print_scripts', 'um_theme_customizer_add_scripts', 30 );
add_action( 'um_theme_header_profile_before', 'um_theme_header_friend_request_modal', 8 );
add_action( 'um_theme_header_profile_before', 'um_theme_header_activity_modal', 10 );
add_action( 'wp_enqueue_scripts', 'um_theme_remove_old_css', 100 );
add_action( 'wp_footer', 'um_theme_um_profile_structured_data', 20 );
/**
 * Rearrange Notification Icon.
 */
if ( class_exists( 'UM' ) && function_exists( 'um_groups_plugins_loaded' ) ) {
    add_action( 'init', 'um_theme_apply_group_list_layout' );
}
/**
 * Rearrange Notification Icon.
 */
if ( function_exists( 'um_notifications_check_dependencies' ) ) {
   add_action( 'init', 'um_notification_rearrrange', 2 );
}


if ( ! function_exists( 'um_notification_rearrrange' ) ) {
    function um_notification_rearrrange() {
        if ( function_exists( 'um_notifications_check_dependencies' ) ) {
            remove_action( 'wp_footer', 'um_notification_show_feed', 99999999999 );
            add_action( 'um_theme_header_profile_before', 'um_theme_header_notification_modal', 10 );
        }
    }
}

add_action( 'init', 'force_um_profile_sidebar', 2 );

function force_um_profile_sidebar(){
    add_action( 'um_profile_menu', 'um_theme_profile_layout_one_open', 10 );
    add_action( 'um_profile_menu_after', 'um_theme_profile_layout_one_close', 10 );
    add_action( 'um_profile_menu_after', 'um_theme_profile_layout_one_sidebar', 15 );
    add_action( 'um_profile_menu_after', 'um_theme_profile_layout_one_close', 20 );
}

/**
 * Remove Download Chata History link from Messenger.
 */

        global $defaults;
        if ( function_exists( 'um_messaging_plugins_loaded' ) ) {
            if ( absint( $defaults['um_theme_ext_pm_message_hide_chat_history'] ) === 2 ) {
                remove_action( 'um_messaging_after_conversation_links', array( UM()->Messaging_API()->GDPR(), 'render_download_chat_link' ), 99, 2 );
                remove_action( 'um_messaging_after_conversations_list', array( UM()->Messaging_API()->GDPR(), 'render_download_all_chats_link' ), 99 );
            }
        }


/**
 * When Customizing Ultimate Member Plugin Pages re-direct to pages based on UM Customizer Tab.
 */
if ( ! function_exists( 'um_theme_customizer_add_scripts' ) ) {
    function um_theme_customizer_add_scripts() {
            if ( class_exists( 'UM' ) ) {
            ?>
                <script type="text/javascript">
                    jQuery( document ).ready( function( $ ) {

                        wp.customize.section( 'customizer_section_um_member_directory', function( section ) {
                            section.expanded.bind( function( isExpanded ) {
                                if ( isExpanded ) {
                                    wp.customize.previewer.previewUrl.set( '<?php echo esc_js( um_get_core_page( 'members' ) ); ?>' );
                                }
                            } );
                        } );

                        wp.customize.section( 'customizer_section_um_profile_template', function( section ) {
                            section.expanded.bind( function( isExpanded ) {
                                if ( isExpanded ) {
                                    wp.customize.previewer.previewUrl.set( '<?php echo esc_js( um_get_core_page( 'user' ) ); ?>' );
                                }
                            } );
                        } );

                    } );
                </script>
            <?php
        }
    }
}

/**
 * Outputs the Messenger Modal in Header.
 */
if ( ! function_exists( 'um_theme_header_activity_modal' ) ) {
    function um_theme_header_activity_modal() {

        global $defaults;

        if ( is_user_logged_in() && class_exists( 'UM' ) && function_exists( 'um_messaging_plugins_loaded' ) && absint( $defaults['um_show_header_messenger'] ) === 1 ) {
            $count = UM()->Messaging_API()->api()->get_unread_count( get_current_user_id() );
            ?>
            <div class="header-messenger-box">
            <div class="dropdown msg-drop">

                <i class="um-msg-tik-ico far fa-envelope dropdown-togglu" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                <?php if ( absint( $count ) !== 0 ) : ;?>
                    <span class="um-message-live-count"><?php echo absint( $count );?></span>
                <?php endif;?>
                <ul class="dropdown-menu msg-drop-menu" aria-labelledby="dropdownMenuButton">
                    <?php um_theme_get_recent_messages();?>
                </ul>
            </div>
            </div>
        <?php
        }
    }
}


/**
 * Outputs the Messenger Modal in Header.
 */
if ( ! function_exists( 'um_theme_header_friend_request_modal' ) ) {
    function um_theme_header_friend_request_modal() {

        global $defaults;

        if ( is_user_logged_in() && class_exists( 'UM' ) && function_exists( 'um_friends_plugins_loaded' ) && absint( $defaults['um_show_header_friend_requests'] ) === 1 ) {
            $count = UM()->Friends_API()->api()->count_friend_requests_received( get_current_user_id() );
            ?>
            <div class="header-friend-requests">
            <div class="dropdown msg-drop">

                <i class="um-friend-tick fas fa-user-friends dropdown-togglu" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                <?php if ( absint( $count ) !== 0 ) : ?>
                    <span class="um-friend-req-live-count"><?php echo absint( $count );?></span>
                <?php endif;?>
                <ul class="dropdown-menu friends-drop-menu" aria-labelledby="dropdownMenuButton">
                    <div class="um-theme-dropdown-header">
                       <h6 class="boot-m-0"><?php esc_html_e( 'Friend Requests', 'um-theme' );?></h6>
                    </div>
                    <?php um_theme_header_get_friend_requests();?>
                </ul>
            </div>
            </div>
        <?php
        }
    }
}


/**
 * Outputs the Messenger Modal in Header.
 */
if ( ! function_exists( 'um_theme_header_get_friend_requests' ) ) {
    function um_theme_header_get_friend_requests() {
        wp_enqueue_script( 'um_friends' );
        wp_enqueue_style( 'um_friends' );

        $friend_request     = UM()->Friends_API()->api()->friend_reqs( get_current_user_id() );
        $user_id            = get_current_user_id();
        $note               = __( 'You do not have pending friend requests yet.', 'um-theme' );

        if ( $friend_request ) {
            foreach ( $friend_request as $k => $arr ) {

                extract( $arr );

                if ( $user_id2 == $user_id ) {
                    $user_id2 = $user_id1;
                }

                um_fetch_user( $user_id2 ); ?>

                <div class="um-friends-user">
                <div class="boot-row">
                    <div class="boot-col-6 boot-col-md-2">
                        <a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-friends-user-photo" title="<?php echo um_user('display_name'); ?>">
                            <?php echo get_avatar( um_user('ID'), 50 ); ?>
                        </a>
                    </div>

                    <div class="um-friends-user-name boot-col-6 boot-col-md-3">
                        <a href="<?php echo esc_url( um_user_profile_url() ); ?>" title="<?php echo um_user('display_name'); ?>">
                            <?php echo esc_attr( um_user('display_name') ); ?>
                        </a>
                    </div>

                    <div class="um-friends-user-btn boot-col-12 boot-col-md-7">
                        <?php
                            if ( $user_id2 === get_current_user_id() ) {
                                echo '<a href="' . um_edit_profile_url() . '" class="um-friend-edit um-button um-alt">' . __('Edit profile','um-theme') . '</a>';
                            } else {
                                echo UM()->Friends_API()->api()->friend_button( $user_id2, get_current_user_id(), true );
                            }
                        ?>
                    </div>
                </div>
                </div>

            <?php }
        } else { ?>

            <div class="um-profile-note">
                <span><?php echo esc_attr( $note ); ?></span>
            </div>

        <?php }
    }
}



/**
 * UM Profile Page wrapper opening.
 */
if ( ! function_exists( 'um_theme_profile_layout_one_open' ) ) {
    function um_theme_profile_layout_one_open() {
        ?>
        <div class="um-profile-content-container">
        <div class="boot-row">
            <div class="<?php um_get_profile_sidebar_status();?> um-theme-profile-single-content">
            <div class="um-theme-profile-single-content-container">
    <?php
    }
}

/**
 * UM Profile Page sidebar status.
 */
if ( ! function_exists( 'um_get_profile_sidebar_status' ) ) {
    function um_get_profile_sidebar_status() {
        if ( is_active_sidebar( 'sidebar-profile' ) ) {
            echo 'boot-col-md-8';
        } else {
            echo 'boot-col-md-12';
        }
    }
}

/**
 * UM Profile Page wrapper closing.
 */
if ( ! function_exists( 'um_theme_profile_layout_one_close' ) ) {
    function um_theme_profile_layout_one_close() {
        echo '</div>';
        echo '</div>';
    }
}

/**
 * UM Profile Page Sidebar.
 */
if ( ! function_exists( 'um_theme_profile_layout_one_sidebar' ) ) {
    function um_theme_profile_layout_one_sidebar() {
        if ( is_active_sidebar( 'sidebar-profile' ) ) { ?>
            <div class="boot-col-md-4 um-theme-profile-single-sidebar">
            <div class="um-theme-profile-single-sidebar-container">
                <?php dynamic_sidebar( 'sidebar-profile' ); ?>
            </div>
            </div>
        <?php }
    }
}

/**
 * Get the Private Messeage Conversation from Database.
 */
if ( ! function_exists( 'um_theme_get_recent_messages' ) ) {
    function um_theme_get_recent_messages() {

        global $wpdb;

        $table_name    = $wpdb->prefix . "um_conversations";
        $user_id       = get_current_user_id();
        $sql_query     = "SELECT * FROM {$table_name} WHERE user_a = %d OR user_b = %d ORDER BY last_updated DESC LIMIT 3";

        $results = $wpdb->get_results( $wpdb->prepare( $sql_query, $user_id, $user_id ) );

        if ( $results ) {
            $value = json_decode( wp_json_encode( $results ), true );
            ?>
                <div class="um-theme-dropdown-header">
                   <h6 class="boot-m-0"><?php esc_html_e( 'Messages', 'um-theme' );?></h6>
                </div>

            <?php
                foreach ( $value as $key ) :
                    echo um_get_conversation( $key['user_a'], $key['user_b'] , $key['conversation_id'] );
                endforeach;
            ?>

            <a href="<?php echo esc_url( add_query_arg( 'profiletab', 'messages', um_user_profile_url( get_current_user_id() ) ) );?>">
                <small class="meta msg-see-all"><?php esc_html_e( 'See All Messages', 'um-theme' );?></small>
            </a>

        <?php } else { ?>
            <p class="no-messages">
                <i class="no-messages-icon far fa-comment-alt"></i>
                <?php esc_html_e( 'No Messages', 'um-theme' );?>
            </p>
            <?php
        }
    }
}

/**
 * Prints out the Private Messeage Conversation.
 */
if ( ! function_exists( 'um_get_conversation' ) ) {
    function um_get_conversation( $user1, $user2, $conversation_id = null ) {

        global $wpdb;
        $table_name2 = $wpdb->prefix . "um_messages";

        // No conversation yet.
        if ( ! $conversation_id || $conversation_id <= 0 ) return;

        // Get conversation ordered by time and show only 1000 messages.
        $messages = $wpdb->get_results( $wpdb->prepare(
            "SELECT *
            FROM {$table_name2}
            WHERE conversation_id = %d
            ORDER BY time DESC LIMIT 3",
            $conversation_id
        ) );

        $response       = null;
        $update_query   = false;
        $messages_link  = add_query_arg( array( 'profiletab' => 'messages', 'conversation_id' => $conversation_id ), um_user_profile_url( get_current_user_id() ) );

        $value = json_decode( wp_json_encode( $messages ), true );
        ?>

        <?php if ( isset( $value[0] ) ) { ?>
            <a href="<?php echo esc_url( $messages_link );?>" class="message-status-<?php echo $value[0]['status'];?>">
            <div class="boot-row header-msg-holder">

                <div class="boot-col-2 header-msg-ava">
                    <?php
                        if ( get_current_user_id() === absint( $value[0]['recipient'] ) ) {
                            echo get_avatar( $value[0]['author'], 40 );
                        } else {
                            echo get_avatar( $value[0]['recipient'], 40 );
                        }
                    ?>
                </div>

                <div class="boot-col-10 header-msg-con">
                <div class="boot-row">
                    <?php if ( get_current_user_id() === absint( $value[0]['recipient'] ) ) : ?>

                        <div class="boot-col-8 messenger-username">
                            <strong><?php echo esc_attr( get_user_meta( $value[0]['author'], 'first_name', true ) ) . '&nbsp' . esc_attr( get_user_meta( $value[0]['author'], 'last_name', true ) );?></strong>
                        </div>
                        <div class="boot-col-4 boot-text-right">
                            <span class="meta"><?php echo UM()->Messaging_API()->api()->beautiful_time( $value[0]['time'], 'right_m' );?></span>
                        </div>

                    <?php else : ?>
                        <div class="boot-col-8 messenger-username">
                            <strong><?php echo esc_attr( get_user_meta( $value[0]['recipient'], 'first_name', true ) ) . '&nbsp' . esc_attr( get_user_meta( $value[0]['recipient'], 'last_name', true ) );?></strong>
                        </div>
                        <div class="boot-col-4 boot-text-right">
                            <span class="meta"><?php echo UM()->Messaging_API()->api()->beautiful_time( $value[0]['time'], 'right_m' );?></span>
                        </div>

                    <?php endif; ?>
                </div>
                <p class="messenger-text"><?php echo wp_kses_post( $value[0]['content'] );?></p>
                </div>
            </div>
            </a>
    <?php }
    }
}

/**
 * UM Dequeue Old UM CSS File.
 * Removes the um-old-default.css
 */
if ( ! function_exists( 'um_theme_remove_old_css' ) ) {
    function um_theme_remove_old_css() {
       wp_dequeue_style( 'um_default_css' );
    }
}

/**
 * Components for Profile Layout One
 */
if ( ! function_exists( 'um_theme_below_profile_layout_one_image_open' ) ) {
    function um_theme_below_profile_layout_one_image_open() {
        echo '<div class="um-below-profile-one">';
    }
}

if ( ! function_exists( 'um_theme_below_profile_layout_one_image_close' ) ) {
    function um_theme_below_profile_layout_one_image_close() {
        echo '</div>';
    }
}


/**
 * Components for Profile Layout One
 */
if ( ! function_exists( 'um_theme_below_profile_layout_two_image_open' ) ) {
    function um_theme_below_profile_layout_two_image_open() {
        echo '<div class="um-below-profile-two">';
    }
}

if ( ! function_exists( 'um_theme_below_profile_layout_two_image_close' ) ) {
    function um_theme_below_profile_layout_two_image_close() {
        echo '</div>';
    }
}


if ( ! function_exists( 'um_theme_friends_add_button' ) ) {
    function um_theme_friends_add_button( $args ) {
        if ( function_exists( 'um_friends_plugins_loaded' ) ) {
            $user_id = absint( um_profile_id() );
            echo '<div class="um-friends-nocoverbtn" style="display: block">' . UM()->Friends_API()->api()->friend_button( $user_id, get_current_user_id() ) . '</div>';
        }
    }
}


if ( ! function_exists( 'um_theme_friend_box_profile' ) ) {
    function um_theme_friend_box_profile() {
        global $defaults;
        if ( function_exists( 'um_friends_plugins_loaded' ) && absint( $defaults['um_show_profile_friend_requests'] ) === 1  ) {

            $friends_defaults = array(
                'user_id'       => ( um_is_core_page( 'user' ) ) ? um_profile_id() : get_current_user_id(),
                'style'         => 'default',
                'max'           => 12
            );

            $args = wp_parse_args( $friends_defaults );
            extract( $args );

            ob_start();

            $friends    = UM()->Friends_API()->api()->friends( $user_id );
            $count      = UM()->Friends_API()->api()->count_friends_plain( $user_id );
        ?>
            <div class="um-friends-list" data-max="<?php echo absint( $max );?>">
                <div class="um-friends-list-header">
                    <p class="um-friends-list-header-title">
                        <?php esc_html_e( 'Friends', 'um-theme' );?> - <span class="um-accent-color"><?php echo absint( $count );?></span>
                    </p>
                </div>
            <?php $total_friends_count = 0; ?>
            <?php if ( $friends ) { ?>

                <?php foreach ( $friends as $k => $arr ) {
                    extract( $arr );
                    $total_friends_count++;

                    if ( $user_id2 == $user_id ) {
                        $user_id2 = $user_id1;
                    }

                    um_fetch_user( $user_id2 );
                ?>

                    <div class="um-friends-list-user">
                        <a href="<?php echo esc_url( um_user_profile_url() ); ?>" title="<?php echo esc_attr( um_user( 'display_name' ) ); ?>">
                        <div class="um-friends-list-pic">
                            <?php echo get_avatar( um_user( 'ID' ), 40 ); ?>
                        </div>
                        <p class="um-friends-list-name"><?php echo esc_attr( um_user( 'first_name' ) ); ?></p>
                        </a>
                    </div>

                <?php } ?>

            <?php } else { ?>
                <p><?php echo ( $user_id === get_current_user_id()  ) ? __( 'You do not have any friends yet.','um-theme' ) : __( 'This user does not have any friends yet.','um-theme' ); ?></p>
            <?php } ?>

            </div>

        <?php
                $user_friends_box = ob_get_contents();
                ob_end_clean();
                echo $user_friends_box;
        }
    }
}


if ( ! function_exists( 'um_theme_um_profile_structured_data' ) ) {
    function um_theme_um_profile_structured_data() {
        if ( class_exists( 'UM' ) ) {

            global $ultimatemember;
            if ( um_is_core_page('user') && um_get_requested_user() ) {

                um_fetch_user( um_get_requested_user() );
                $content    = um_convert_tags( um_get_option( 'profile_desc' ) );
                $user_id    = um_user( 'ID' );
                $url        = um_user_profile_url();
                $avatar     = um_get_user_avatar_url( $user_id, 'original' );
                um_reset_user();
                ?>

                <script type='application/ld+json'>
                {
                    "@context": "http://schema.org/",
                    "@type": "ProfilePage",
                    "name": "<?php echo um_get_display_name( $user_id ); ?>",
                    "url": "<?php echo esc_url($url); ?>",
                    "thumbnailUrl": "<?php echo $avatar; ?>",
                    "description": "<?php echo $content; ?>"
                }
                </script>
            <?php
            }
        }
    }
}

/**
 * UM Notification from Notifications_API
 */
if ( ! function_exists( 'um_theme_notification_show_feed' ) ) {
    function um_theme_notification_show_feed() {

        if ( ! is_user_logged_in() ) {
            return;
        }

        $notifications  = UM()->Notifications_API()->api()->get_notifications( 6 );
        $unread         = (int) UM()->Notifications_API()->api()->get_notifications( 0, 'unread', true );
        $unread_count   = ( absint( $unread ) > 9 ) ? '+9' : $unread;
        ?>

        <div class="um-theme-dropdown-header">
        <div class="boot-row">
            <div class="boot-col-md-6"><h6 class="boot-m-0"><?php esc_html_e( 'Notifications', 'um-theme' );?></h6></div>
            <div class="boot-col-md-6 boot-text-right">
                <a href="<?php echo esc_url( UM()->account()->tab_link( 'webnotifications' ) ); ?>" class="um-notification-i-settings">
                    <i class="um-faicon-cog"></i>
                </a>
            </div>
        </div>
        </div>

    <?php

    if ( ! $notifications ) { ?>
        <div class="um-header-notifications-none"><?php _e( 'No new notifications', 'um-theme' ); ?></div>
    <?php
    } else {
        foreach ( $notifications as $notification ) {
            if ( ! isset( $notification->id ) ) {
                continue;
            } ?>

            <div class="um-notification <?php echo $notification->type;?> <?php echo $notification->status;?>" data-notification_id="<?php echo $notification->id; ?>" data-notification_uri="<?php echo esc_url( $notification->url ); ?>">

                <img src="<?php echo esc_url( um_secure_media_uri( $notification->photo ) );?>" data-default="<?php echo esc_url( um_secure_media_uri( um_get_default_avatar_uri() ) );?>" class="um-notification-photo">

                <?php echo stripslashes( $notification->content ); ?>

                <span class="b2" data-time-raw="<?php echo $notification->time;?>">
                    <?php echo UM()->Notifications_API()->api()->get_icon( $notification->type ); ?><?php echo UM()->Notifications_API()->api()->nice_time( $notification->time ); ?>
                </span>

                <span class="um-notification-hide">
                    <a href="javascript:void(0);"><i class="um-icon-android-close"></i></a>
                </span>

            </div>

        <?php } ?>
        <div class="meta notfication-see-all">
            <a href="<?php echo esc_url( um_get_core_page( 'notifications' ) );?>"><?php _e( 'See All Notifications', 'um-theme' ); ?></a>
        </div>
<?php
        ob_end_flush();
    }

    }
}

/**
 * Outputs the Notification Modal in Header.
 */
if ( ! function_exists( 'um_theme_header_notification_modal' ) ) {
    function um_theme_header_notification_modal() {

        global $defaults;

        if ( is_user_logged_in() && class_exists( 'UM' ) && function_exists( 'um_notifications_check_dependencies' ) && absint( $defaults['um_show_header_notification'] ) === 1 ) {

            $notifications  = UM()->Notifications_API()->api()->get_notifications( 6 );
            $unread         = (int) UM()->Notifications_API()->api()->get_notifications( 0, 'unread', true );
            $unread_count   = ( absint( $unread ) > 9 ) ? '+9' : $unread;
            ?>

            <div class="header-notification-box">
            <div class="dropdown msg-drop">
                <i class="um-notification-ico far fa-bell dropdown-togglu" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                <?php if ( absint( $unread_count ) !== 0 ) : ;?>
                    <span class="um-notification-live-count"><?php echo absint( $unread_count );?></span>
                <?php endif;?>
                <ul class="dropdown-menu msg-drop-menu" aria-labelledby="dropdownMenuButton">
                    <?php um_theme_notification_show_feed();?>
                </ul>
            </div>
            </div>
        <?php
        }
    }
}


if ( ! function_exists( 'um_theme_apply_group_list_layout' ) ) {
    function um_theme_apply_group_list_layout(){
        remove_action('um_groups_directory','um_groups_directory');
        remove_action('um_groups_directory_search_form','um_groups_directory_search_form');
        remove_action('um_groups_directory_tabs','um_groups_directory_tabs');
        add_action('um_groups_directory','um_theme_um_groups_directory');
        add_action('um_groups_directory_tabs','um_theme_um_groups_directory_tabs');
        add_action('um_groups_directory_search_form','um_theme_um_groups_directory_search_form');
    }
}

/**
 * Display groups directory
 */
if ( ! function_exists( 'um_theme_um_groups_directory' ) ) {
function um_theme_um_groups_directory( $args ){
    global $defaults;
    wp_enqueue_script( 'um_groups' );
    wp_enqueue_style( 'um_groups' );

    if ( um_groups('total_groups') > 0 ) {

        if ( absint( $defaults['um_theme_ext_um_group_list_layout'] ) === 1 ) {
            echo '<div class="um-groups-directory">';
        } else {
            echo '<div class="um-groups-directory boot-row">';
        }

        foreach( um_groups('groups') as $group ):
                $slug = UM()->Groups()->api()->get_privacy_slug( $group->ID );
                $count = um_groups_get_member_count( $group->ID );
                ?>

                <?php if ( absint( $defaults['um_theme_ext_um_group_list_layout'] ) === 1 ) : ?>
                <?php
                echo '<div class="um-group-item">';

                    if( true == $args['show_actions'] ) {
                        echo '<div class="actions">';
                        echo '<ul>';

                            echo '<li>';
                                do_action('um_groups_join_button', $group->ID );
                            echo '</li>';

                            echo '<li class="count-members">';
                            echo '<span class="group-meta-info">';
                            echo sprintf( _n( '<span>%s</span> member', '<span>%s</span> members', $count, 'um-theme' ), number_format_i18n( $count ) );
                            echo '</span>';
                            echo '</li>';

                            echo '<li class="last-active">';
                            echo '<span class="group-meta-info">';
                                echo '<span>';
                                echo __('active, ','um-theme').human_time_diff( UM()->Groups()->api()->get_group_last_activity( $group->ID, true ) ).__(' ago','um-theme');
                                echo '</span>';
                            echo '</span>';
                            echo '</li>';

                        echo '</ul>';
                        echo '</div>';
                    }

                    echo '<a href="'.get_permalink( $group->ID ).'">';
                        if( 'small' == $args['avatar_size'] ){
                            echo UM()->Groups()->api()->get_group_image( $group->ID, 'default', 50, 50 );
                        }else{
                            echo UM()->Groups()->api()->get_group_image( $group->ID, 'default', 100, 100 );
                        }
                        echo "<h4 class='um-group-name'>".get_the_title( $group->ID )."</h4>";
                    echo '</a>';

                    echo '<div class="um-group-meta">';
                        echo '<ul>';
                        echo '<li class="privacy">';
                        echo '<span class="group-meta-info">';
                        echo um_groups_get_privacy_icon( $group->ID );
                        echo sprintf( __('%s Group', 'um-theme' ), um_groups_get_privacy_title( $group->ID ) );
                        echo '</span>';
                        echo '</li>';
                        echo '<li class="description">' ;
                        echo $group->post_content;
                        echo '</li>';
                        echo '</ul>';
                    echo '</div>';
                    echo '<div class="um-clear"></div>';


                echo '</div>';
                echo '<div class="um-clear"></div>';
                ?>
                    <?php elseif ( absint( $defaults['um_theme_ext_um_group_list_layout'] ) === 2 ) : ?>
                        <div class="boot-col-md-12">
                        <div class="um-group-item slim-box-container boot-row">
                        <div class="boot-col-md-2 um-group-image-container">
                            <a href="<?php echo esc_url( get_permalink( $group->ID ) );?>">
                                <?php
                                    if( 'small' == $args['avatar_size'] ){
                                        echo UM()->Groups()->api()->get_group_image( $group->ID, 'default', 50, 50 );
                                    }else{
                                        echo UM()->Groups()->api()->get_group_image( $group->ID, 'default', 250, 250 );
                                    }
                                ?>
                            </a>
                        </div>

                        <div class="boot-col-md-7 um-group-text-container">
                            <a href="<?php echo esc_url( get_permalink( $group->ID ) );?>"><h4 class='um-group-name'><?php echo get_the_title( $group->ID );?></h4></a>

                            <div class="um-group-meta">
                                <ul>
                                    <li class="privacy">
                                        <span class="group-meta-info">
                                        <?php echo um_groups_get_privacy_icon( $group->ID );?>
                                        <?php echo sprintf( __('%s Group', 'um-theme' ), um_groups_get_privacy_title( $group->ID ) );?>
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <?php if ( $defaults['um_theme_ext_um_group_show_group_description'] == true ) : ?>
                                <p class="group-description"><?php echo $group->post_content;?></p>
                            <?php endif;?>
                        </div>

                        <div class="boot-col-md-3 um-group-button-container">
                            <?php if( true == $args['show_actions'] ) : ?>
                                <div class="actions">
                                    <ul>
                                        <li><?php do_action('um_groups_join_button', $group->ID );?></li>
                                    </ul>
                                </div>
                            <?php endif;?>

                                <?php if( true == $args['show_actions'] ) : ?>
                                    <div class="actions">
                                        <ul>
                                            <li class="count-members">
                                                <span class="group-meta-info">
                                                <?php esc_html_e( 'Total Members:', 'um-theme' );?>
                                                <?php echo sprintf( _n( '<span>%s</span> member', '<span>%s</span> members', $count, 'um-theme' ), number_format_i18n( $count ) );?>
                                                </span>
                                            </li>
                                            <li class="last-active">
                                                <span class="group-meta-info">
                                                <?php esc_html_e( 'Recent Activity:', 'um-theme' );?>
                                                <span><?php echo human_time_diff( UM()->Groups()->api()->get_group_last_activity( $group->ID, true ) ).__(' ago','um-theme');?></span>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif;?>


                        </div>
                        </div>
                        </div>
                    <?php else : ?>
                        <div class="group-grid boot-col-md-4">
                        <div class="group-grid-inner">

                            <div class="um-group-image-container">
                                <a href="<?php echo esc_url( get_permalink( $group->ID ) );?>">
                                    <?php
                                        if( 'small' == $args['avatar_size'] ){
                                            echo UM()->Groups()->api()->get_group_image( $group->ID, 'default', 50, 50 );
                                        }else{
                                            echo UM()->Groups()->api()->get_group_image( $group->ID, 'default', 250, 250 );
                                        }
                                    ?>
                                </a>
                            </div>

                            <div class="um-group-text-container">
                                <a href="<?php echo esc_url( get_permalink( $group->ID ) );?>"><h4 class='um-group-name'><?php echo get_the_title( $group->ID );?></h4></a>
                                <div class="um-group-meta">
                                    <ul>
                                        <li class="privacy">
                                            <span class="group-meta-info">
                                            <?php echo um_groups_get_privacy_icon( $group->ID );?>
                                            <?php echo sprintf( __('%s Group', 'um-theme' ), um_groups_get_privacy_title( $group->ID ) );?>
                                            </span>
                                        </li>
                                    </ul>
                                </div>

                            <?php if ( $defaults['um_theme_ext_um_group_show_group_description'] == true ) : ?>
                                <p class="group-description"><?php echo $group->post_content;?></p>
                            <?php endif;?>

                            </div>

                            <div class="um-group-button-container">


                                <?php if( true == $args['show_actions'] ) : ?>
                                    <div class="actions">
                                        <ul>
                                            <li class="count-members">
                                                <span class="group-meta-info">
                                                <?php esc_html_e( 'Total Members:', 'um-theme' );?>
                                                <?php echo sprintf( _n( '<span>%s</span> member', '<span>%s</span> members', $count, 'um-theme' ), number_format_i18n( $count ) );?>
                                                </span>
                                            </li>
                                            <li class="last-active">
                                                <span class="group-meta-info">
                                                <?php esc_html_e( 'Recent Activity:', 'um-theme' );?>
                                                <span><?php echo human_time_diff( UM()->Groups()->api()->get_group_last_activity( $group->ID, true ) ).__(' ago','um-theme');?></span>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif;?>



                                <?php if( true == $args['show_actions'] ) : ?>
                                    <div class="actions">
                                        <ul>
                                            <li><?php do_action('um_groups_join_button', $group->ID );?></li>
                                        </ul>
                                    </div>
                                <?php endif;?>

                            </div>

                        </div>
                        </div>
                    <?php endif; ?>

        <?php endforeach;?>

       </div>
       <?php
        // Restore original Post Data
    } else {
        _e('No groups found.','um-theme');
    }
}
}


/**
 * Groups directory tabs
 */
if ( ! function_exists( 'um_theme_um_groups_directory_tabs' ) ) {
    function um_theme_um_groups_directory_tabs( $args ){
        wp_enqueue_script( 'um_groups' );
        wp_enqueue_style( 'um_groups' );

        if( false == $args['show_total_groups_count'] || um_is_core_page('my_groups') ) return;

        $filter = get_query_var('filter');
        ?>

        <div id="um-groups-filters" class="um-groups-found-posts">
            <ul class="filters">
                <li class="all <?php echo ( 'all' == $filter || empty( $filter ) ? 'active': '' );?>">
                    <a href="<?php echo esc_url( um_get_core_page('groups') );?>" class="group-filter-item-link">
                        <span class="group-filter-item"><?php echo sprintf( __('All Groups <span>%s</span>','um-theme'), um_groups_get_all_groups_count() );?></span>
                    </a>
                </li>

                <?php if( is_user_logged_in() ) : ?>
                    <li class="own <?php echo ( 'own' == $filter ? 'active': '' );?>">
                        <a href="<?php echo esc_url( um_get_core_page('groups') );?>?filter=own" class="group-filter-item">
                            <?php echo sprintf( __('My Groups <span>%s</span>', 'um-theme'), um_groups_get_own_groups_count() );?>
                        </a>
                    </li>
                    <li class="create">
                        <a href="<?php echo esc_url( um_get_core_page('create_group') );?>" class="group-filter-item">
                            <?php echo __('Create a Group', 'um-theme');?>
                        </a>
                    </li>
                <?php endif;?>
            </ul>
        </div>
    <?php
    }
}


/**
 * Group directory search form
 */
if ( ! function_exists( 'um_theme_um_groups_directory_search_form' ) ) {
    function um_theme_um_groups_directory_search_form( $args ){

        if( 0 == $args['show_search_form'] ) return;

        wp_enqueue_script( 'um_groups' );
        wp_enqueue_style( 'um_groups' );

        $search = get_query_var('groups_search');
        $filter = get_query_var('filter');
        ?>


        <div class="um-groups-directory-header">
        <form class="um-groups-search-form">

        <div class="boot-row">
            <div class="boot-col-md-7">
                <?php echo '<input type="text" name="groups_search" placeholder="'.__('Search groups...', 'um-theme' ).'" value="'.esc_attr( $search ).'"/>';?>
            </div>
            <div class="group-search-filter boot-col-md-5">
                <?php

                if( 1 == $args['show_search_categories'] ){

                    $cat = get_query_var('cat');

                    $arr_categories = um_groups_get_categories();

                    echo "<select name=\"cat\">";
                    echo "<option value=\"\">".__("All Categories","um-theme")."</option>";
                    if( ! empty( $arr_categories ) ){
                        foreach( $arr_categories as $value => $title ){
                            echo "<option value=\"{$value}\" ". selected( $cat, $value, false ) .">{$title}</option>";
                        }
                    }
                    echo "</select>";

                }

                if( 1 == $args['show_search_tags'] ){

                    $tags = get_query_var('tags');

                    $arr_tags = um_groups_get_tags();

                    echo "<select name=\"tags\" >";
                    echo "<option value=\"\">".__("All Tags","um-theme")."</option>";
                    if( ! empty( $arr_tags ) ){
                        foreach( $arr_tags as $value => $title ){
                            echo "<option value=\"{$value}\" ". selected( $tags, $value, false ) ." >{$title}</option>";
                        }
                    }
                    echo "</select>";

                }

                if( 'own' == $filter ){
                    echo '<input type="hidden" name="filter" value="'. esc_attr( $filter ) .'" />';
                }

                echo '<input type="submit" class="" value="'.__('Search', 'um-theme' ).'"/> ';
                echo '<a href="'. get_the_permalink() .'" class="primary-button">'.__('Clear', 'um-theme' ).'</a>';
                ?>
            </div>
        </div>
        </form>
         <div class="um-clear"></div>
        </div>
        <?php
    }
}