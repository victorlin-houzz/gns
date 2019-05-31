<?php if ( ! defined( 'ABSPATH' ) ) exit;

$args = UM()->Groups()->api()->get_members( get_the_ID(), 'blocked','', '', 0 );

$args['group_id'] = get_the_ID();

$args = apply_filters('um_groups_user_lists_args', $args );
$args = apply_filters('um_groups_user_lists_args__blocked', $args ); ?>

<div class="um-groups-wrapper">
	<?php UM()->Groups()->api()->get_template("list-users", $args ); ?>
</div>