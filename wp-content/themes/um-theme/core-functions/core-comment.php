<?php
/**
 * Helper functions for Comments section.
 *
 * @package     um-theme
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

/*
 * Comment Markup
 */
if ( ! function_exists( 'um_theme_comment' ) ) {
    function um_theme_comment( $comment, $args, $depth ) {
            ?>
            <li <?php comment_class();?> id="comment-<?php comment_ID();?>">
            <div class="boot-row comment-body">

                <div class="boot-col-md-2 boot-col-3 comment-body-left comment-meta commentmetadata">
                    <div class="comment-author author vcard">
                        <?php echo get_avatar( $comment, 60 ); ?>
                    </div>
                    <?php if ( '0' == $comment->comment_approved ) { ?>
                        <em class="comment-awaiting-moderation"><?php esc_attr_e( 'Your comment is awaiting moderation.', 'um-theme' ); ?></em>
                        <br />
                    <?php } ?>
                </div>

                <div class="boot-col-md-10 boot-col-9 comment-body__right comment-content" id="div-comment-<?php comment_ID() ?>">
                    <h5 class="comment__author body-font-family"><?php echo get_comment_author_link(); ?></h5>

                    <div class="meta-block">
                        <a href="<?php echo esc_url( htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ); ?>" class="meta comment__meta comment-date">
                        <time datetime="<?php echo get_comment_date( 'Y-m-d H:i:s' );?>">
                            <span class="meta comment__date"><?php comment_date(); ?></span>
                            <span class="meta comment__time"><?php comment_time(); ?></span>
                        </time>
                        </a>

                        <button class="comment__edit boot-text-right">
                            <?php edit_comment_link( esc_html__( 'Edit', 'um-theme' ), '  ', '' ); ?>
                        </button>
                        <button class="meta button--reply boot-text-left">
                            <?php
                                comment_reply_link( array_merge( $args, array(
                                    'reply_text'    => esc_html__( 'Reply', 'um-theme' ),
                                    'depth'         => $depth,
                                    'max_depth'     => $args['max_depth'],
                            ) ) );
                            ?>
                        </button>
                    </div>

                    <div class="comment__body comment-text">
                        <?php comment_text(); ?>
                    </div>

                </div>

            </div>
            </li>
        <?php
    }
}
