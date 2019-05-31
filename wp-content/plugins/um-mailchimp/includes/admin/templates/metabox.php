<p class="sub"><?php _e('Connection status','um-mailchimp'); ?></p>

<?php $result = UM()->Mailchimp_API()->api()->get_account_data();
if ( is_wp_error( $result ) ) { ?>

	<p><span class="red"><?php echo $result->get_error_message(); ?></span></p>

<?php } else {
	if ( isset( $result['account_name'] ) ) { ?>
		<p>
			<?php printf( __( 'Your site is successfully <strong><span class="ok">linked</span></strong> to <strong>%s</strong> MailChimp account.', 'um-mailchimp' ), $result['account_name'] ); ?>
		</p>
	<?php } ?>

	<p class="sub">
		<?php _e( 'Account status', 'um-mailchimp' ); ?>
	</p>

	<?php $external_lists = UM()->Mailchimp_API()->api()->get_lists(); ?>
	<p><?php printf( _n('%d subscriber',' %d subscribers',$result['total_subscribers'],'um-mailchimp' ), $result['total_subscribers'] ); ?>
	<p><?php printf( _n('%d Mailchimp list','%d Mailchimp lists',count( $external_lists ), 'um-mailchimp'), count( $external_lists ) ); ?></p>

	<?php $internal_lists = UM()->Mailchimp_API()->api()->get_lists( false ); ?>
	<p><?php printf( _n('%d UM Mailchimp list','%d UM Mailchimp lists',count( $internal_lists ), 'um-mailchimp'), count( $internal_lists ) ); ?></p>

	<script type="text/html" id="tmpl-um-mailchimp-sync-metabox">
		<div class="um_mailchimp_metabox">
			<select name="um_mailchimp_sync_list" class="um_mailchimp_sync_list"  style="width:100px;">
				<option value=""><?php _e('All Lists', 'um-mailchimp' ); ?></option>
				<# for( index in data.internal_lists ) { #>
					<option value="<# print( index ) #>" <# if( data.internal_lists == index ) { #>selected="selected"<# } #>><# print( data.internal_lists[ index ] ) #></option>
				<# } #>
			</select>
			<a href="#" id="btn_um_mailchimp_sync_now" class="um-btn-mailchimp-progress-start button <# if( data.button_disabled ) { #>disabled<# } #>"><?php _e('Sync Now','um-mailchimp'); ?></a>
			<# if( data.message ) { #>
				<span class="um-progress-message-area">
					<# if( data.loading ) { #>
						<span class="spinner"></span>
					<# } #>
					<span class="um-progress-message"><# print( data.message ) #></span>
				</span>
			<# } #>
		</div>
	</script>

	<script type="text/html" id="tmpl-um-mailchimp-subscribe-metabox">
		<div class="um_mailchimp_metabox">
			<# if( ! data.step ) { #>
				<select name="um_mailchimp_user_role" class="um_mailchimp_user_role"  style="width:100px;">
					<option value=""><?php _e( 'All Roles', 'um-mailchimp' ) ?></option>
					<# for( index in data.roles ) { #>
						<option value="<# print( index ) #>" <# if( data.role == index ) { #>selected="selected"<# } #>><# print( data.roles[ index ] ) #></option>
					<# } #>
				</select>
				<select name="um_mailchimp_user_status" class="um_mailchimp_user_status" style="width:100px;">
					<option value=""><?php _e( 'All Status', 'um-mailchimp' ) ?></option>
					<# for( index in data.status_list ) { #>
						<option value="<# print( index ) #>" <# if( data.status == index ) { #>selected="selected"<# } #>><# print( data.status_list[ index ] ) #></option>
					<# } #>
				</select>
				<a href="javascript:void(0);" id="btn_um_mailchimp_scan_now" class="um-btn-mailchimp-progress-start button <# if( data.button_disabled ) { #>disabled<# } #>"><?php _e('Scan Now','um-mailchimp'); ?></a>
			<# } else if( data.step == 2 ) { #>
				<select name="um_mailchimp_list" class="um_mailchimp_list"  style="width:100px;">
					<option value=""><?php _e('All Lists', 'um-mailchimp' ); ?></option>
					<# for( index in data.internal_lists ) { #>
						<option value="<# print( index ) #>" <# if( data.internal_lists == index ) { #>selected="selected"<# } #>><# print( data.internal_lists[ index ] ) #></option>
					<# } #>
				</select>
				<a href="javascript:void(0);" id="btn_um_mailchimp_bulk_subscribe" class="um-btn-mailchimp-progress-start button <# if( data.button_disabled ) { #>disabled<# } #>"><?php _e('Opt-in now','um-mailchimp'); ?></a>
			<# } #>
			<# if( data.message ) { #>
				<span class="um-progress-message-area">
					<# if( data.loading ) { #>
						<span class="spinner"></span>
					<# } #>
					<span class="um-progress-message"><# print( data.message ) #></span>
				</span>
			<# } #>
		</div>
	</script>

    <p class="sub"><?php _e( 'Sync Profiles','um-mailchimp' ); ?></p>
	<div id="um-mailchimp-sync-metabox-wrapper"></div>
    <br />
	<p class="sub"><?php _e( 'Bulk Opt-in','um-mailchimp' ); ?></p>
	<div id="um-mailchimp-subscribe-metabox-wrapper"></div>
<?php } ?>