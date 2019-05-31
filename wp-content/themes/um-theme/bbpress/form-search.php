<?php
/**
 * Search
 *
 * @package bbPress
 * @subpackage Theme
 */
?>

<form role="search" method="get" class="search-form" id="bbp-search-form" action="<?php bbp_search_url(); ?>">
	<div>
		<label for="bbp_search">
			<span class="screen-reader-text"><?php echo esc_attr( 'Search for:', 'label', 'um-theme' ); ?></span>
			<input type="hidden" name="action" value="bbp-search-request" />
			<input tabindex="<?php bbp_tab_index(); ?>" type="search" placeholder="<?php echo esc_attr_e( 'Search', 'um-theme' ); ?>" value="<?php echo esc_attr( bbp_get_search_terms() ); ?>" name="bbp_search" id="bbp_search" />
		</label>

		<button type="submit" class="search-submit" style="display: none;">
			<span class="screen-reader-text"><?php echo esc_attr( 'Search', 'submit button', 'um-theme' ); ?></span>
		</button>
	</div>
</form>