<?php
class UM_myCRED_Setup_Hooks{

	function __construct() {

		add_filter( 'mycred_setup_hooks', array( $this,'register_custom_hooks'), 10, 2 );
		add_filter('mycred_all_references',array( $this, 'references' ), 10, 1 );
	
	}

	public function references( $hooks ){

		$hooks['update_account'] = __('UM - Updating Account','um-mycred');
		$hooks['um_user_login'] = __('UM - Logging Into Site','um-mycred');
		$hooks['member_search'] = __('UM - Using Search Member Form','um-mycred');
		$hooks['profile_photo'] = __('UM - Uploading Profile Photo','um-mycred');
		$hooks['cover_photo'] = __('UM - Uploading Cover Photo','um-mycred');
		$hooks['update_profile'] = __('UM - Updating Profile','um-mycred');
		$hooks['signup'] = __('UM - Completing Registration','um-mycred');
		
		return $hooks;
	}

	public function register_custom_hooks( $installed, $point_type ) {

		/** 
		 * Core Hooks
		 */
		
		// Login
		$installed['um-user-login'] = array(
			'title'        => 'UM - Login',
			'description'  => 'Award points for login hooks',
			'callback'     => array( 'UM_myCRED_Login_Hooks' )
		);
		
		// Register
		$installed['um-user-register'] = array(
			'title'        => 'UM - Register',
			'description'  => 'Award points for register hooks',
			'callback'     => array( 'UM_myCRED_Register_Hooks' )
		);

		// Profile
		$installed['um-user-profile'] = array(
			'title'        => 'UM - Profile',
			'description'  => 'Award points for profile hooks',
			'callback'     => array( 'UM_myCRED_Profile_Hooks' )
		);

		// Account
		$installed['um-user-account'] = array(
			'title'        => 'UM - Account',
			'description'  => 'Award points for account hooks',
			'callback'     => array( 'UM_myCRED_Account_Hooks' )
		);

		// Member Directory
		$installed['um-member-directory'] = array(
			'title'        => 'UM - Member Directory',
			'description'  => 'Award points for Member Directory hooks',
			'callback'     => array( 'UM_myCRED_Member_Directory_Hooks' )
		);
		

		$installed = apply_filters('um_mycred_hooks_installed__filter', $installed );

		return $installed;

	}

}

new UM_myCRED_Setup_Hooks();