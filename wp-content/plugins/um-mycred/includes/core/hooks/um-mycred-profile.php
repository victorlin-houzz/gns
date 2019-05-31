<?php
/**
 * Social Login Connect Hooks
 */
class UM_myCRED_Profile_Hooks extends myCRED_Hook {

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {

		$this->um_hooks = array(
			'profile_photo' => array(
				'title'  => 'Upload Profile Photo',
				'action' => 'adding profile photo'
			),
			'remove_profile_photo' => array(
				'title'  => 'Remove Profile Photo',
				'action' => 'removing profile photo',
				'deduct' => true,
			),
			'cover_photo' => array(
				'title'  => 'Upload Cover Photo',
				'action' => 'adding cover photo'
			),
			'remove_cover_photo' => array(
				'title'  => 'Remove Cover Photo',
				'action' => 'removing cover photo',
				'deduct' => true,
			),
			'update_profile' => array(
				'title'  => 'Update Profile',
				'action' => 'updating profile'
			)
		);

		$arr_defaults = array();

		foreach( $this->um_hooks as $hook => $k ){

			$arr_defaults[ $hook ] = array(
				'creds'   => 1,
				'log'     => "%plural% for {$k['action']}.",
				'limit'  => '0/x',
				'um_hook' => $hook,
				'notification_tpl' => '',
			);

		}

		parent::__construct( array(
			'id'       => 'um-user-profile',
			'defaults' => $arr_defaults
		), $hook_prefs, $type );

	}

	/**
	 * Hook into WordPress
	 */
	public function run() {
	
		add_action('um_before_upload_db_meta_profile_photo', array($this,'award_points_putting_profile_photo'), 1 );
		add_action('um_before_upload_db_meta_cover_photo', array($this,'award_points_putting_cover_photo'), 1 );
		add_action('um_user_pre_updating_profile', array($this,'award_points_updating_profile'), 1 );
		add_action('um_after_remove_profile_photo', array($this,'deduct_when_user_remove_photo'), 1 );
		add_action('um_after_remove_cover_photo', array($this,'deduct_when_user_remove_cover'), 1 );
	
	
	
	}

	/**
	 * Check if the user qualifies for points
	 */
	public function award_points_putting_profile_photo( $to_update ) {

		if( ! $user_id  ){
			$user_id = get_current_user_id();
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit
		if ( $this->over_hook_limit( 'profile_photo', 'um-user-profile', $user_id ) ) return;

		// Execute
		$this->core->add_creds(
			'um-user-profile',
			$user_id,
			$this->prefs[ 'profile_photo' ]['creds'],
			$this->prefs[ 'profile_photo' ]['log'],
			0,
			'',
			$this->mycred_type
		);
	}

	/**
	 * Check if the user qualifies for points
	 */
	public function award_points_putting_cover_photo( $user_id ) {
		
		if( ! $user_id  ){
			$user_id = get_current_user_id();
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit
		if ( $this->over_hook_limit( 'cover_photo', 'um-user-profile', $user_id ) ) return;

		// Execute
		$this->core->add_creds(
			'um-user-profile',
			$user_id,
			$this->prefs[ 'cover_photo' ]['creds'],
			$this->prefs[ 'cover_photo' ]['log'],
			0,
			'',
			$this->mycred_type
		);
	}

	/**
	 * Check if the user qualifies for points
	 */
	public function award_points_updating_profile( $changes ) {

		$user_id = get_current_user_id();

		if( um_is_core_page('register') )  return;
		if( is_admin() )  return;
		
		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit
		if ( $this->over_hook_limit( 'update_profile', 'um-user-profile', $user_id ) ) return;

		$changed = array();
		um_fetch_user( $user_id );
		
		foreach( $changes as $k => $v ) {
			$value = um_user( $k );
			if ( $value !== $v || is_array( $value ) && is_array( $v ) && count( array_intersect( $value, $v ) ) > 0 ) {
				$changed[ $k ] = $v;
			}
		}

		if ( isset( $changed['mycred_default'] ) ){
			unset( $changed['mycred_default'] );
		}

		
		if ( isset( $changed ) && !empty( $changed ) ) {
			// Execute
			$this->core->add_creds(
				'um-user-profile',
				$user_id,
				$this->prefs[ 'update_profile' ]['creds'],
				$this->prefs[ 'update_profile' ]['log'],
				0,
				'',
				$this->mycred_type
			);
		}

	}

	/**
	 * Check if the user qualifies for points
	 */
	public function deduct_when_user_remove_photo( $user_id ) {

		if( ! $user_id  ){
			$user_id = get_current_user_id();
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit
		if ( $this->over_hook_limit( 'remove_profile_photo', 'um-user-profile', $user_id ) ) return;

		$creds = -1 * abs( $this->prefs[ 'remove_profile_photo' ]['creds'] ); 

		// Execute
		$this->core->add_creds(
			'um-user-profile',
			$user_id,
			$creds,
			$this->prefs[ 'remove_profile_photo' ]['log'],
			0,
			'',
			$this->mycred_type
		);
	}

	/**
	 * Check if the user qualifies for points
	 */
	public function deduct_when_user_remove_cover( $user_id ) {

		if( ! $user_id  ){
			$user_id = get_current_user_id();
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit
		if ( $this->over_hook_limit( 'remove_cover_photo', 'um-user-profile', $user_id ) ) return;

		$creds = -1 * abs( $this->prefs[ 'remove_cover_photo' ]['creds'] ); 

		// Execute
		$this->core->add_creds(
			'um-user-profile',
			$user_id,
			$creds,
			$this->prefs[ 'remove_cover_photo' ]['log'],
			0,
			'',
			$this->mycred_type
		);
	}


	/**
	 * Add Settings
	 */
	public function preferences() {
		// Our settings are available under $this->prefs
		$prefs = $this->prefs; ?>

			<?php foreach( $this->um_hooks as $hook => $k ):?> 
				<hr/>
				<h2><?php echo $k['title']; ?></h2>
				<!-- First we set the amount -->
				<?php if( isset( $this->um_hooks[ $hook ]['deduct'] ) ): ?>
					<label class="subheader">Deduct <?php echo $this->core->plural(); ?></label>
				<?php else: ?>
					<label class="subheader">Award <?php echo $this->core->plural(); ?></label>
				<?php endif; ?>
				<ol>
					<li>
						<div class="h2"><input type="text" name="<?php echo $this->field_name( array( $hook, 'creds' ) ); ?>" id="<?php echo $this->field_id( array( $hook, 'creds' ) ); ?>" value="<?php echo $this->core->format_number( $prefs[ $hook ]['creds'] ); ?>" size="8" /></div>
					</li>
				</ol>
				<!-- Then the log template -->
				<label class="subheader"><?php _e( 'Log template', 'mycred' ); ?></label>
				<ol>

					<li>
						<div class="h2"><input type="text" name="<?php echo $this->field_name(  array( $hook, 'log' )  ); ?>" id="<?php echo $this->field_id(  array( $hook, 'log' ) ); ?>" value="<?php echo $prefs[ $hook ]['log']; ?>" class="long" /></div>
					</li>
					<li>
						<label for="<?php echo $this->field_id(  array( $hook, 'limit' ) ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
						<?php echo $this->hook_limit_setting( $this->field_name(  array( $hook, 'limit' ) ), $this->field_id(   array( $hook, 'limit' )  ), $prefs[ $hook ]['limit'] ); ?>
					</li>
					<input type="hidden" name="<?php echo $this->field_name( array( $hook, 'um_hook' ) ); ?>" value="<?php echo $hook;?>"/>
				</ol>

				<?php do_action( 'um_mycred_hooks_option_extended', $hook, $k, $prefs, $this ) ?>
				<ol>
					<li class="empty">&nbsp;</li>
				</ol>

			<?php endforeach; ?>
			<?php
			
	}

	/**
	 * Sanitize Preferences
	 */
	public function sanitise_preferences( $data ) {
		$new_data = $data;
   
		foreach( $this->um_hooks as $hook => $k ){
			// Apply defaults if any field is left empty
			$new_data[ $hook ]['creds'] = ( !empty( $data[ $hook ]['creds'] ) ) ? $data[ $hook ]['creds'] : $this->defaults[ $hook ]['creds'];
			$new_data[ $hook ]['log'] 	= ( !empty( $data[ $hook ]['log'] ) ) 	? sanitize_text_field( $data[ $hook ]['log'] ) : $this->defaults[ $hook ]['log'];
			$limit = ( !empty( $data[ $hook ]['limit'] ) ) ? sanitize_text_field( $data[ $hook ]['limit'] ) : $this->defaults[ $hook ]['limit'];
			$new_data[ $hook ]['limit_by'] = ( !empty( $data[ $hook ]['limit_by'] ) ) ? sanitize_text_field( $data[ $hook ]['limit_by'] ) : $this->defaults[ $hook ]['limit_by'];
			
			$new_data[ $hook ]['notification_tpl'] 	= ( !empty( $data[ $hook ]['notification_tpl'] ) ) 	? sanitize_text_field( $data[ $hook ]['notification_tpl'] ) : $this->defaults[ $hook ]['notification_tpl'];
			

			if ( $limit != '' ){
				$new_data[ $hook ]['limit'] = $limit . '/' . $new_data[ $hook ]['limit_by'];
				unset( $new_data[ $hook ]['limit_by'] );
			}

		}

		$new_data = apply_filters("um_mycred_sanitise_pref", $new_data );

		return $new_data;
	}
}