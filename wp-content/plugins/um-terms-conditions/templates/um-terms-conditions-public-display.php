<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://ultimatemember.com/
 * @since      1.0.0
 *
 * @package    Um_Terms_Conditions
 * @subpackage Um_Terms_Conditions/public/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="um-field um-field-type_terms_conditions"  data-key="use_terms_conditions_agreement" style="display: block;padding:0px">
	<div class="um-field-area">
		<div class='um-terms-conditions-content' style="display:none">
			<?php
			if( ! empty( $args['use_terms_conditions_content_id']  ) ){
				$um_content_query = get_post( $args['use_terms_conditions_content_id'] );
				if ( isset( $um_content_query ) ){
					echo apply_filters( 'um_terms_conditions_tc_page_content', $um_content_query->post_content, $args );
				}
			}
			?>
		</div>
		<a href="javascript:;" class="um-toggle-terms" data-toggle-state="hidden"
		   data-toggle-show="<?php echo ! empty( $args['use_terms_conditions_toggle_show'] ) ? $args['use_terms_conditions_toggle_show'] : __( 'Show Terms','um-terms-conditions' ); ?>"
		   data-toggle-hide="<?php echo ! empty( $args['use_terms_conditions_toggle_hide'] ) ? $args['use_terms_conditions_toggle_hide'] : __( 'Hide Terms','um-terms-conditions' ); ?>">
			<?php echo ! empty( $args['use_terms_conditions_toggle_show'] ) ? $args['use_terms_conditions_toggle_show'] : __( 'Show Terms','um-terms-conditions' ); ?>
		</a>
	</div>
	<div class="um-field-area">
		
		<label class="um-field-checkbox">
			<input type="checkbox" name="use_terms_conditions_agreement" value="1">
			<span class="um-field-checkbox-state">
				<i class="um-icon-android-checkbox-outline-blank"></i>
			</span>
			<span class="um-field-checkbox-option">
				<?php echo ! empty( $args['use_terms_conditions_agreement'] ) ? $args['use_terms_conditions_agreement'] :  __( 'Please confirm that you agree to our terms & conditions','um-terms-conditions' ); ?>
			</span>
		</label>
		<div class="um-clear"></div>

		<?php $errors = UM()->form()->errors;

		if ( isset( $errors['use_terms_conditions_agreement'] ) ){

			$error_message = ! empty( $args['use_terms_conditions_error_text'] ) ? $args['use_terms_conditions_error_text'] :  __( 'You must agree to our terms & conditions','um-terms-conditions' );

			echo '<p class="um-notice err"><i class="um-icon-ios-close-empty" onclick="jQuery(this).parent().fadeOut();"></i>' . $error_message . '</p><br/>';
		} ?>

		<div class="um-clear"></div>
	</div>
</div>