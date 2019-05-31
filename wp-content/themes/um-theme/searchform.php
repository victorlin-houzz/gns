<?php
/**
 * Template for displaying search forms in UM Theme
 * @package um-theme
 */

?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="search-form-label">
		<span class="screen-reader-text"><?php echo esc_attr( 'Search for:', 'label', 'um-theme' ); ?></span>
		<input type="search" id="search-form-input" class="search-field" placeholder="<?php echo esc_attr_e( 'Search', 'um-theme' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	</label>
	<button type="submit" class="search-submit" style="display: none;">
		<span class="screen-reader-text"><?php echo esc_attr( 'Search', 'submit button', 'um-theme' ); ?></span>
	</button>
</form>

