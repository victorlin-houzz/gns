<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $is_my_profile ) ) {
	$is_my_profile = um_is_myprofile();
}

UM()->Photos_API()->get_view(
	'template-parts/gallery-head',
	array(
		'is_my_profile' =>  $is_my_profile
	)
);

$user_id = um_user('ID');
if ( isset( $_POST['user_id'] ) ) {
	$user_id = $_POST['user_id'];
}

$albums = new WP_Query( array(
	'post_type'         => 'um_user_photos',
	'author__in'        => array( $user_id ),
	'posts_per_page'    => -1,
	'post_status'       => 'publish'
) );

if ( $albums->have_posts() ) { ?>
	<div class="um-user-photos-albums">

		<?php while( $albums->have_posts() ) {
			$albums->the_post();
			$photos = get_post_meta( get_the_ID(), '_photos', true );
			if ( $photos ) {
				$count     = count( $photos );
				$count_msg = sprintf( _n( '%s Photo', '%s Photos', $count, 'um-user-photos' ), number_format_i18n( $count ) );
			} else {
				$count_msg = false;
			}
			$data = array(
				'title'     => get_the_title(),
				'id'        => get_the_ID(),
				'count_msg' => $count_msg
			);
			UM()->Photos_API()->get_view( 'template-parts/album-block', $data );
		} ?>

		<div class="um-clear"></div>
	</div>

<?php } else { ?>
	<p class="text-center"><?php _e( 'Nothing to display', 'um-user-photos' ) ?></p>
<?php }

wp_reset_postdata();