<?php
/**
 * Social Login Connect Hooks
 */
class UM_myCRED_Account_Hooks extends myCRED_Hook {

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {

		$this->um_hooks = array(
			'update_account' => array(
				'title'  => 'Account Updated',
				'action' => 'updating account'
			)
		);

		$arr_defaults = array();

		foreach ( $this->um_hooks as $hook => $k ) {

			$arr_defaults[ $hook ] = array(
				'creds'   => 1,
				'log'     => "%plural% for {$k['action']}.",
				'limit'  => '0/x',
				'um_hook' => $hook,
				'notification_tpl' => "You've gained %plural% for {$k['action']}.",
			);

		}

		parent::__construct( array(
			'id'       => 'um-user-account',
			'defaults' => $arr_defaults
		), $hook_prefs, $type );
	}

	/**
	 * Hook into WordPress
	 */
	public function run() {
	
		add_action('um_after_user_account_updated', array($this,'award_points_account_updated'), 20 );
	
	}

	/**
	 * Check if the user qualifies for points
	 */
	public function award_points_account_updated( $user_id ) {
		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit
		if ( $this->over_hook_limit( 'update_account', 'um-user-account', $user_id ) ) return;

		// Execute
		$this->core->add_creds(
			'um-user-account',
			$user_id,
			$this->prefs[ 'update_account' ]['creds'],
			$this->prefs[ 'update_account' ]['log'],
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
				<label class="subheader">Award <?php echo $this->core->plural(); ?></label>
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
			
			$new_data[ $hook ]['notification_tpl'] 	= ( !empty( $data[ $hook ]['notification_tpl'] ) ) 	?  $data[ $hook ]['notification_tpl'] :  $this->defaults[ $hook ]['notification_tpl'] ;
			

			if ( $limit != '' ){
				$new_data[ $hook ]['limit'] = $limit . '/' . $new_data[ $hook ]['limit_by'];
				unset( $new_data[ $hook ]['limit_by'] );
			}

		}

		$new_data = apply_filters("um_mycred_sanitise_pref", $new_data );


		return $new_data;
	}
}
