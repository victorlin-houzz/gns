<div class="um-message-emoji">
	<a href="javascript:void(0);" class="um-message-emo">
		<img src="<?php echo um_messaging_url . 'assets/img/emoji_init.png'; ?>" alt="" title="" />
	</a>
	<span class="um-message-emolist">

		<?php foreach( UM()->Messaging_API()->api()->emoji as $emoji_code => $emoji_url ) { ?>

			<span class="um-message-insert-emo" data-emo="<?php echo esc_attr( $emoji_code ); ?>" title="<?php echo esc_attr( $emoji_code ); ?>">
				<img class="emoji" src="<?php echo esc_attr( $emoji_url ); ?>" title="<?php echo esc_attr( $emoji_code ); ?>" alt="<?php echo esc_attr( $emoji_code ); ?>" />
			</span>

		<?php } ?>

	</span>
</div>