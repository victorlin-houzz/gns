<div class="um-reviews-header">

	<span class="um-reviews-header-span"><?php echo UM()->Reviews_API()->api()->rating_header(); ?></span>
	
	<span class="um-reviews-avg" data-number="5" data-score="<?php echo UM()->Reviews_API()->api()->get_rating(); ?>"></span>
	
</div>

<div class="um-reviews-avg-rating"><?php echo UM()->Reviews_API()->api()->avg_rating(); ?></div>

<div class="um-reviews-details">
	<?php UM()->Reviews_API()->api()->get_details(); ?>
	
	<?php if ( UM()->Reviews_API()->api()->get_filter() ) { ?>
	
		<span class="um-reviews-filter"><?php printf(__('(You are viewing only %s star reviews. <a href="%s">View all reviews</a>)','um-reviews'), UM()->Reviews_API()->api()->get_filter(), remove_query_arg('filter') ); ?></span>
	
	<?php } ?>
	
</div>