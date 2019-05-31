<?php
/**
 * UM Theme Helper Functions
 *
 * @package     um-theme
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

global $defaults;

/**
 *Add Class to the body
 */
if ( ! function_exists( 'um_theme_body_classes' ) ) {
    function um_theme_body_classes( $classes ) {
        global $defaults;
        // Multiple Author
        if ( is_multi_author() ) {
        $classes[] = 'group-blog';
        }

        // Not Singular
        if ( ! is_singular() ) {
            $classes[] = 'hfeed';
        }

        // Add a class if there is a custom header.
        if ( has_header_image() ) {
            $classes[] = 'has-header-image';
        }

        $classes[] = 'boot-container-fluid';

        return $classes;
    }
}

/**
 * Add CSS class to Next Pagination
 */
if ( ! function_exists( 'um_theme_next_page_add_class' ) ) {
    function um_theme_next_page_add_class() {
        return 'class="pagination-next"';
    }
}

/**
 * Add CSS class to Previous Pagination
 */
if ( ! function_exists( 'um_theme_previous_page_add_class' ) ) {
    function um_theme_previous_page_add_class() {
        return 'class="pagination-previous"';
    }
}

/**
 * Prints HTML with meta information for the Tags
 */
if ( ! function_exists( 'um_post_tag' ) ) {
    function um_post_tag() {
        // Hide category and tag text for pages.
        if ( get_post_type() === 'post' ) {
            /* translators: used between list items, there is a space after the comma */
            $tags_list = get_the_tag_list();
            if ( $tags_list && ! is_wp_error( $tags_list ) ) {
                echo '<span class="meta meta-tag">' . wp_kses_post( $tags_list ) . '</span>';
            }
        }
    }
}

/**
 * Prints Post published date
 */
if ( ! function_exists( 'um_published_on' ) ) {
    function um_published_on() { ?>
        <span class="meta post-meta__time">
            <time datetime="<?php echo get_the_date( 'Y-m-d H:i:s' );?>"><?php echo get_the_date( get_option( 'date_format' ) );?></time>
        </span>
    <?php }
}

/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
if ( ! function_exists( 'um_post_author' ) ) {
    function um_post_author() {
        ?>
      <span class="meta post-meta__author">
        <span class="vcard author author_name">
        <span class="fn">
          <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );?>" class="post-meta-author-link">
            <?php the_author();?>
          </a>
        </span>
        </span>
      </span>
<?php
    }
}

/**
 * Numbered Pagination
 */
if ( ! function_exists( 'um_theme_pagination' ) ) {
    function um_theme_pagination() {
      global $defaults;
      $blog_pagination_type = $defaults['um_theme_blog_pagination_type'];

        if ( $blog_pagination_type === 1 ) :
            // Next & Previous Page Pagination.
            echo '<div class="article-pagination-custom boot-col-12">';
            next_posts_link( 'Next Page', 0 );
            previous_posts_link( 'Previous Page', 0 );
            echo '</div>';
        else :
            // Numeric Pagination.
            echo '<div class="boot-col-md-12">';
            echo '<div class="article-pagination-num">';
                the_posts_pagination( array(
                    'prev_text' => '<i class="fas fa-angle-left"></i>',
                    'next_text' => '<i class="fas fa-angle-right"></i>',
                ) );
            echo '</div>';
            echo '</div>';
        endif;
    }
}


/**
 * Check & Prints out Scroll To Top if activated
 */
if ( ! function_exists( 'um_theme_scroll_to_top' ) ) {
    function um_theme_scroll_to_top() {
      global $defaults;
        if ( $defaults['um_theme_show_scroll_to_top'] === 1 ) { ?>
      <script>
      // Scroll To Top
      jQuery(window).scroll(function(){
        if (jQuery(this).scrollTop() > 700) {
            jQuery('.scrollToTop').fadeIn();
        } else {
            jQuery('.scrollToTop').fadeOut();
        }
        });

        //Click event to scroll to top
        jQuery('.scrollToTop').click(function(){
          jQuery('html, body').animate({scrollTop : 0},1000);
                return false;
        });
        </script>
    <?php }}
}


/**
 * Print out the post categories.
 */
if ( ! function_exists( 'um_theme_category' ) ) {
    function um_theme_category() {
        if ( 'post' === get_post_type() ) {
        /* translators: used between list items, there is a space after the comma */
        $categories_list = get_the_category_list( esc_html__( ', ', 'um-theme' ) );
            if ( $categories_list && um_theme_has_active_categories() ) {
                echo '<p class="entry-meta">';
                echo '<span class="meta meta-category" rel="tag">' . wp_kses_post( $categories_list ) . '</span>';
                echo '</p>';
            }
        }
    }
}

/**
 * Check if the site has active categories.
 *
 * We will store the result in a transient so this function
 * can be called frequently without any performance concern.
 *
 * @see   um_theme_has_active_categories_reset()
 *
 * @since 1.0.0
 *
 * @uses [get_transient](https://developer.wordpress.org/reference/functions/get_transient/)
 * @uses [get_categories](https://developer.wordpress.org/reference/functions/get_categories/)
 * @uses [set_transient](https://developer.wordpress.org/reference/functions/set_transient/)
 *
 * @return bool Returns true when categories are found, otherwise returns false.
 */
if ( ! function_exists( 'um_theme_has_active_categories' ) ) {
    function um_theme_has_active_categories() {

        $has_active_categories = get_transient( 'um_theme_has_active_categories' );

        if ( WP_DEBUG || false === $has_active_categories ) {

            $categories = get_categories(
                array(
                    'fields'     => 'ids',
                    'hide_empty' => 1,
                    'number'     => 2, // We only care if more than one exists.
                )
            );

            $has_active_categories = ( count( $categories ) > 1 );

            set_transient( 'um_theme_has_active_categories', $has_active_categories );

        }

        /**
         * Filter if the site has active categories.
         *
         * @since 1.0.0
         *
         * @var bool
         */
        return (bool) apply_filters( 'um_theme_has_active_categories', ! empty( $has_active_categories ) );
    }
}


/**
 * Reset the transient for the active categories check.
 *
 * @action create_category
 * @action edit_category
 * @action delete_category
 * @action save_post
 * @see    um_theme_has_active_categories()
 * @since  1.0.0
 */
if ( ! function_exists( 'um_theme_has_active_categories_reset' ) ) {
    function um_theme_has_active_categories_reset() {
        delete_transient( 'um_theme_has_active_categories' );
    }
}

/**
* Article Author Box
*/
if ( ! function_exists( 'um_get_single_article_author_box' ) ) {
    function um_get_single_article_author_box() {

        $avatar             = get_avatar( get_the_author_meta( 'user_email' ), 75 );
        $author_name        = get_the_author_meta( 'display_name' );
        $author_bio         = get_the_author_meta( 'description' );
        $author_post_url    = esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );
        ?>

        <div class="article-author-box boot-text-center">
            <div class="author-box-profile">
                <div class="author-image">
                    <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );?>" class="post-meta-author-link">
                        <?php echo wp_kses_post( $avatar );?>
                    </a>
                </div>
                <?php if ( $author_name ) : ?>
                    <h5 class="author-name">
                        <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );?>" class="post-meta-author-link">
                            <?php echo esc_attr( $author_name );?>
                        </a>
                    </h5>
                <?php endif;?>

                <?php if ( $author_bio ) : ?>
                    <p class="meta author-bio"><?php echo esc_attr( $author_bio );?></p>
                <?php endif;?>
            </div>
        </div>
        <?php
    }
}

/**
 * Displays the navigation to next/previous post, when applicable.
 *
 * @param array $args Optional. See get_the_post_navigation() for available arguments.
 *                    Default empty array.
 */
if ( ! function_exists( 'um_theme_the_post_navigation' ) ) {
    function um_theme_the_post_navigation( $args = array() ) {
        echo wp_kses_post( um_theme_get_the_post_navigation( $args ) );
    }
}

/**
 * Retrieves the navigation to next/previous post, when applicable.
 *
 * @param array $args {
 *     Optional. Default post navigation arguments. Default empty array.
 *
 * @type string $prev_text Anchor text to display in the previous post link. Default '%title'.
 * @type string $next_text Anchor text to display in the next post link. Default '%title'.
 * @type bool $in_same_term Whether link should be in a same taxonomy term. Default false.
 * @type array|string $excluded_terms Array or comma-separated list of excluded term IDs. Default empty.
 * @type string $taxonomy Taxonomy, if `$in_same_term` is true. Default 'category'.
 * @type string $screen_reader_text Screen reader text for nav element. Default 'Post navigation'.
 * }
 * @return string Markup for post links.
 */
if ( ! function_exists( 'um_theme_get_the_post_navigation' ) ) {
    function um_theme_get_the_post_navigation( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'prev_text'          => '%title',
            'next_text'          => '%title',
            'in_same_term'       => false,
            'excluded_terms'     => '',
            'taxonomy'           => 'category',
            'screen_reader_text' => __( 'Post navigation', 'um-theme' ),
        ) );

        $navigation = '';

        $previous = get_previous_post_link(
            '<div class="nav-previous"><span class="nav-previous-title">%link</span>' . '<span class="nav-previous-info">' . __( 'Previous', 'um-theme' ) . '</span></div>',
            $args['prev_text'],
            $args['in_same_term'],
            $args['excluded_terms'],
            $args['taxonomy']
        );

        $next = get_next_post_link(
            '<div class="nav-next"><span class="nav-next-title">%link</span>' . '<span class="nav-next-info">' . __( 'Next', 'um-theme' ) . '</span></div>',
            $args['next_text'],
            $args['in_same_term'],
            $args['excluded_terms'],
            $args['taxonomy']
        );

        // Only add markup if there's somewhere to navigate to.
        if ( $previous || $next ) {
            $navigation = _navigation_markup( $previous . $next, 'post-navigation', $args['screen_reader_text'] );
        }

        return $navigation;
    }
}

/**
 * Google Fonts URL
 * @link https://fonts.google.com/
 * @license SIL Open Font License (OFL)
 * @since 1.0.0
 */
if ( ! function_exists( 'um_theme_fonts_url' ) ) {
    function um_theme_fonts_url() {

        $fonts_url          = '';
        $content_font       = get_theme_mod( 'um_theme_typography_body_font', 'Open Sans' );
        $header_font        = get_theme_mod( 'um_theme_typography_title_font', 'Open Sans' );
        $navigation_font    = get_theme_mod( 'um_theme_typography_nav_font', 'Open Sans' );
        $logo_font          = get_theme_mod( 'um_theme_typography_logo_font', 'Open Sans' );
        $button_font        = get_theme_mod( 'um_theme_typography_button_font', 'Open Sans' );

        if ( 'off' !== $content_font || 'off' !== $header_font || 'off' !== $navigation_font || 'off' !== $logo_font || 'off' !== $button_font ) {
            $font_families = array();

            if ( 'off' !== $content_font ) {
                $font_families[] = $content_font . ':300,400,700';
            }

            if ( 'off' !== $header_font ) {
                $font_families[] = $header_font . ':300,400,700';
            }

            if ( 'off' !== $navigation_font ) {
                $font_families[] = $navigation_font . ':300,400,700';
            }

            if ( 'off' !== $logo_font ) {
                $font_families[] = $logo_font . ':300,400,700';
            }

            if ( 'off' !== $button_font ) {
                $font_families[] = $button_font . ':300,400,700';
            }

            $protocol = is_ssl() ? 'https' : 'http';
            $query_args = array(
                'family' => rawurlencode( implode( '|', array_unique( $font_families ) ) ),
                'subset' => 'latin,latin-ext',
            );

            $fonts_url = add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" );
        }

        return esc_url_raw( $fonts_url );
    }
}

/**
 * Outputs Social Media Icons
 */
if ( ! function_exists( 'um_theme_social_menu' ) ) {
    function um_theme_social_menu() {
        global $defaults;

        $social_link_one    = esc_url( $defaults['um_theme_social_account_link_one'] );
        $social_link_two    = esc_url( $defaults['um_theme_social_account_link_two'] );
        $social_link_three  = esc_url( $defaults['um_theme_social_account_link_three'] );
        $social_link_four   = esc_url( $defaults['um_theme_social_account_link_four'] );
        $social_link_five   = esc_url( $defaults['um_theme_social_account_link_five'] );
        $social_link_six    = esc_url( $defaults['um_theme_social_account_link_six'] );
        $social_icon_one    = esc_attr( $defaults['um_theme_social_account_icon_one'] );
        $social_icon_two    = esc_attr( $defaults['um_theme_social_account_icon_two'] );
        $social_icon_three  = esc_attr( $defaults['um_theme_social_account_icon_three'] );
        $social_icon_four   = esc_attr( $defaults['um_theme_social_account_icon_four'] );
        $social_icon_five   = esc_attr( $defaults['um_theme_social_account_icon_five'] );
        $social_icon_six    = esc_attr( $defaults['um_theme_social_account_icon_six'] );
    ?>

    <div class="um-theme-social-link" itemscope itemtype="http://schema.org/Organization">
    <link itemprop="url" href="<?php echo esc_url( home_url( '/' ) ); ?>">

    <!-- Social Icon 1 -->
    <?php if ( ! empty( $social_link_one ) ) : ?>
        <a href="<?php echo esc_url( $social_link_one );?>">
            <span class="<?php echo esc_attr( $social_icon_one ); ?>"></span>
        </a>
    <?php endif; ?>

    <!-- Social Icon 2 -->
    <?php if ( ! empty( $social_link_two ) ) : ?>
        <a href="<?php echo esc_url( $social_link_two );?>">
            <span class="<?php echo esc_attr( $social_icon_two ); ?>"></span>
        </a>
    <?php endif; ?>

    <!-- Social Icon 3 -->
    <?php if ( ! empty( $social_link_three ) ) : ?>
        <a href="<?php echo esc_url( $social_link_three );?>">
            <span class="<?php echo esc_attr( $social_icon_three ); ?>"></span>
        </a>
    <?php endif; ?>

    <!-- Social Icon 4 -->
    <?php if ( ! empty( $social_link_four ) ) : ?>
        <a href="<?php echo esc_url( $social_link_four );?>">
            <span class="<?php echo esc_attr( $social_icon_four ); ?>"></span>
        </a>
    <?php endif; ?>

    <!-- Social Icon 5 -->
    <?php if ( ! empty( $social_link_five ) ) : ?>
        <a href="<?php echo esc_url( $social_link_five );?>">
            <span class="<?php echo esc_attr( $social_icon_five ); ?>"></span>
        </a>
    <?php endif; ?>

    <!-- Social Icon 6 -->
    <?php if ( ! empty( $social_link_six ) ) : ?>
        <a href="<?php echo esc_url( $social_link_six );?>">
            <span class="<?php echo esc_attr( $social_icon_six ); ?>"></span>
        </a>
    <?php endif; ?>

    </div>

<?php }
}

/**
 * Output Theme Footer Widgets
 */
if ( ! function_exists( 'um_theme_footer_widgets' ) ) {
    function um_theme_footer_widgets() {

        do_action( 'um_theme_before_footer_widgets' );

        global $defaults;
        $footer_widget_column = $defaults['um_theme_footer_widget_column'];

        if ( is_active_sidebar( 'sidebar-footer-one' ) || is_active_sidebar( 'sidebar-footer-two' ) || is_active_sidebar( 'sidebar-footer-three' ) || is_active_sidebar( 'sidebar-footer-four' ) ) : ?>
            <div class="site-footer-layout-sidebar">
            <div class="boot-container">
                <div class="boot-row">
                    <?php if ( is_active_sidebar( 'sidebar-footer-one' ) ) : ?>
                    <div class="footer-sidebar-column-one <?php echo sanitize_html_class( $footer_widget_column );?>">
                        <?php do_action( 'um_theme_before_first_footer_col' ); ?>
                        <?php dynamic_sidebar( 'sidebar-footer-one' ); ?>
                        <?php do_action( 'um_theme_after_first_footer_col' ); ?>
                    </div>
                    <?php endif;?>

                    <?php if ( is_active_sidebar( 'sidebar-footer-two' ) ) : ?>
                    <div class="footer-sidebar-column-two <?php echo sanitize_html_class( $footer_widget_column );?>">
                        <?php do_action( 'um_theme_before_second_footer_col' ); ?>
                        <?php dynamic_sidebar( 'sidebar-footer-two' ); ?>
                        <?php do_action( 'um_theme_after_second_footer_col' ); ?>
                    </div>
                    <?php endif;?>

                    <?php if ( is_active_sidebar( 'sidebar-footer-three' ) ) : ?>
                    <div class="footer-sidebar-column-three <?php echo sanitize_html_class( $footer_widget_column );?>">
                        <?php do_action( 'um_theme_before_third_footer_col' ); ?>
                        <?php dynamic_sidebar( 'sidebar-footer-three' ); ?>
                        <?php do_action( 'um_theme_after_third_footer_col' ); ?>
                    </div>
                    <?php endif;?>

                    <?php if ( is_active_sidebar( 'sidebar-footer-four' ) ) : ?>
                    <div class="footer-sidebar-column-four <?php echo sanitize_html_class( $footer_widget_column );?>">
                        <?php do_action( 'um_theme_before_fourth_footer_col' ); ?>
                        <?php dynamic_sidebar( 'sidebar-footer-four' ); ?>
                        <?php do_action( 'um_theme_after_fourth_footer_col' ); ?>
                    </div>
                    <?php endif;?>
                </div>
            </div>
            </div>
        <?php endif;

        do_action( 'um_theme_after_footer_widgets' );
    }
}

/**
 * Output Footer Column First Text
 */
if ( ! function_exists( 'um_theme_footer_first_column_text' ) ) {
    function um_theme_footer_first_column_text() {
        global $defaults;

        if ( ! empty( $defaults['um_footer_colum_first_text'] ) ) {

            $content = $defaults['um_footer_colum_first_text'];
            $content = um_theme_make_translation( 'um_footer_colum_first_text', $content );

            echo do_shortcode( wp_kses_post( $content ) );
        }
    }
}

/**
 * Output Footer Column Second Text
 */
if ( ! function_exists( 'um_theme_footer_second_column_text' ) ) {
    function um_theme_footer_second_column_text() {
        global $defaults;

        if ( ! empty( $defaults['um_footer_colum_second_text'] ) ) {

            $content = $defaults['um_footer_colum_second_text'];
            $content = um_theme_make_translation( 'um_footer_colum_second_text', $content );
            echo '<div class="boot-col-md-6 footer-text">';
            echo '<p>';
            echo do_shortcode( wp_kses_post( $content ) );
            echo '</p>';
            echo '</div>';
        }
    }
}


/**
 * Output Theme Footer Content Text
 */
if ( ! function_exists( 'um_theme_footer_bottom_content' ) ) :
    function um_theme_footer_bottom_content() {
        global $defaults;
        ?>

    <div class="site-footer-center site-footer-layout-text">
    <div class="website-canvas">

        <?php if ( absint( $defaults['um_show_footer_layout'] ) === 1 ) : ?>
            <div class="boot-row">
            <?php if ( $defaults['um_footer_colum_first_layout'] === 1 ) : ?>
            <?php
                if ( ! empty( $defaults['um_footer_colum_first_text'] ) ) : ?>
                    <div class="boot-col-md-12 footer-text boot-text-center">
                        <p><?php um_theme_footer_first_column_text();?></p>
                    </div>
            <?php endif;?>
            <?php endif;?>

            <?php if ( absint( $defaults['um_footer_colum_first_layout'] ) === 2 ) : ?>
                <div class="boot-col-md-12 footer-menu-container">
                    <nav class="navbar navbar-light">
                     <div class="boot-container boot-justify-content-center">

                        <?php
                            wp_nav_menu( array(
                                'theme_location'    => 'footer',
                                'depth'             => 1,
                                'container'         => 'div',
                                'container_class'   => 'foot-nav',
                                'container_id'      => 'bs-navbar-footer',
                                'menu_class'        => 'um-nav-footer',
                                'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                                'walker'            => new WP_Bootstrap_Navwalker(),
                            ) );
                        ?>
                    </div>
                    </nav>
                </div>
            <?php endif;?>
            </div>
        <?php endif;?>

        <?php if ( absint( $defaults['um_show_footer_layout'] ) === 2 ) : ?>
            <div class="boot-row">
            <?php if ( absint( $defaults['um_footer_colum_first_layout'] ) === 1 ) : ?>
            <?php
                if ( ! empty( $defaults['um_footer_colum_first_text'] ) ) : ?>
                    <div class="boot-col-md-6 footer-text">
                        <p><?php um_theme_footer_first_column_text();?></p>
                    </div>
            <?php endif;?>
            <?php endif;?>

            <?php if ( absint( $defaults['um_footer_colum_first_layout'] ) === 2 ) : ?>
                <div class="boot-col-md-6 footer-menu-container">
                    <nav class="navbar navbar-light">
                     <div class="boot-container">

                        <?php
                            wp_nav_menu( array(
                                'theme_location'    => 'footer',
                                'depth'             => 1,
                                'container'         => 'div',
                                'container_class'   => 'boot-justify-content-end',
                                'container_id'      => 'bs-navbar-footer',
                                'menu_class'        => 'um-nav-footer',
                                'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                                'walker'            => new WP_Bootstrap_Navwalker(),
                            ) );
                        ?>
                    </div>
                    </nav>
                </div>
            <?php endif;?>


            <?php if ( $defaults['um_footer_colum_second_layout'] === 1 ) : ?>
                <?php um_theme_footer_second_column_text();?>
            <?php endif;?>

            <?php if ( $defaults['um_footer_colum_second_layout'] === 2 ) : ?>
                <div class="boot-col-md-6 footer-menu-container">
                    <nav class="navbar navbar-light">
                     <div class="boot-container boot-justify-content-end">

                        <?php
                            wp_nav_menu( array(
                                'theme_location'    => 'footer',
                                'depth'             => 1,
                                'container'         => 'div',
                                'container_class'   => 'boot-justify-content-end',
                                'container_id'      => 'bs-navbar-footer',
                                'menu_class'        => 'um-nav-footer',
                                'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                                'walker'            => new WP_Bootstrap_Navwalker(),
                            ) );
                        ?>
                    </div>
                    </nav>
                </div>
            <?php endif;?>
            </div>
        <?php endif;?>
    </div>
    </div>
<?php
    }
endif;

/**
 * Output Article Header of Single Post
 */
if ( ! function_exists( 'um_theme_single_post_header' ) ) {
    function um_theme_single_post_header() {

        global $defaults;
        $show_post_cat          = absint( $defaults['um_theme_show_post_category'] );
        $show_article_summary   = absint( $defaults['um_theme_show_post_article_summary'] );
        ?>

        <header class="entry-header single-article-header">
            <?php
                if ( $show_post_cat === 1 ) {
                    um_theme_category();
                }
            ?>

            <h1 class="entry-title"><?php the_title(); ?></h1>

            <?php if ( is_singular( 'post' ) ) : ?>
                <?php if ( $show_article_summary === 1 ) :?>
                    <div class="article-summary"><?php the_excerpt();?></div>
                <?php endif;?>
                <span class="meta"><?php um_post_author();?> - <?php um_published_on();?></span>
            <?php endif;?>
        </header>

    <?php

    }
}

/**
 * Output Article Featured Image
 */
if ( ! function_exists( 'um_theme_single_post_featured_image' ) ) {
    function um_theme_single_post_featured_image() {

        global $defaults;
        $f_image_position          = absint( $defaults['um_theme_post_featured_image_pos'] );

        if ( $f_image_position === 1 ) {
            return;
        } elseif ( $f_image_position === 2 ) {
            add_action( 'um_theme_single_post_top', 'um_theme_single_post_print_featured_image', 5 );
        } elseif ( $f_image_position === 3 ) {
            add_action( 'um_theme_single_post', 'um_theme_single_post_print_featured_image', 15 );
        } else {
            add_action( 'um_theme_single_post', 'um_theme_single_post_beside_print_featured_image', 15 );
        }
    }
}

/**
 * Output Article Featured Image - Regular
 */
if ( ! function_exists( 'um_theme_single_post_print_featured_image' ) ) {
    function um_theme_single_post_print_featured_image() {
        the_post_thumbnail( 'full' );
    }
}

/**
 * Output Article Featured Image - Beside
 */
if ( ! function_exists( 'um_theme_single_post_beside_print_featured_image' ) ) {
    function um_theme_single_post_beside_print_featured_image() {
        echo '<div class="single-post-beside">';
        the_post_thumbnail( 'full' );
        echo '</div>';
    }
}

/**
 * Output Single Post Content
 */
if ( ! function_exists( 'um_theme_single_post_content' ) ) {
    function um_theme_single_post_content() {

        global $defaults;

        $show_post_tag          = absint( $defaults['um_theme_show_post_tags'] );
        $show_author_box        = absint( $defaults['um_theme_show_post_author_box'] );
        $show_comments          = absint( $defaults['um_theme_show_site_comments'] );
        ?>

    <div class="entry-content single-article-content">
    <div class="single-article-content-inner">

        <?php the_content(); ?>
        <?php
            if ( $show_post_tag === 1 ) {
                um_post_tag();
            }
        ?>
        <?php
            if ( $show_author_box === 1 && is_singular( 'post' ) ) :
                um_get_single_article_author_box();
            endif;
        ?>
        <?php if ( $show_comments === 1 ) : ?>
            <div class="single-article-comment">
                <div class="website-canvas">
                <?php
                    if ( comments_open() || get_comments_number() ) {
                        comments_template();
                    }
                ?>
                </div>
            </div>
        <?php endif;?>

    </div>
    </div>
    <?php
    }
}

/**
 * Output Single Post Additional Content
 */
if ( ! function_exists( 'um_theme_single_post_additional_content' ) ) {
    function um_theme_single_post_additional_content() {
        global $defaults;
        $show_post_navigation   = absint( $defaults['um_theme_show_post_meta_navigation'] );
        ?>

        <div class="single-article-additional">
            <div class="website-canvas">
                <?php
                    if ( $show_post_navigation === 1 && is_singular( 'post' ) ) {
                        um_theme_the_post_navigation();
                    }
                ?>
                <?php
                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'um-theme' ),
                        'after'  => '</div>',
                    ) );
                ?>
            </div>
        </div>
    <?php
    }
}

/**
 * Output Page Comments
 */
if ( ! function_exists( 'um_theme_output_page_comments' ) ) {
    function um_theme_output_page_comments() {
        global $defaults;

        if ( $defaults['um_theme_show_site_comments'] == true ) {
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }
        }
    }
}

/**
 * Remove Junk from Headers
 */
if ( ! function_exists( 'um_theme_performance_enhancer' ) ) {
    function um_theme_performance_enhancer() {
        add_filter( 'the_generator', '__return_false' );
        remove_filter( 'wp_title',    'capital_P_dangit', 11 );
        remove_filter( 'the_title',    'capital_P_dangit', 11 );
        remove_filter( 'the_content',  'capital_P_dangit', 11 );
        remove_filter( 'comment_text', 'capital_P_dangit', 31 );
        remove_action( 'wp_head', 'wp_generator' );
        remove_action( 'wp_head', 'rsd_link' );
        remove_action( 'wp_head', 'wlwmanifest_link' );
        remove_action( 'wp_head', 'index_rel_link' );
        remove_action( 'wp_head', 'feed_links', 2 );
        remove_action( 'wp_head', 'feed_links_extra', 3 );
        remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
        remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
        remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
        remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
    }
}

/**
 * Remove Version info from scripts
 */
if ( ! function_exists( 'um_theme_remove_script_version' ) ) {
    function um_theme_remove_script_version( $src ) {
        $parts = explode( '?ver', $src );
        return $parts[0];
    }
}

/**
 * Output the Header Search ( Member or Generic Search Box )
 */
if ( ! function_exists( 'um_theme_header_search_type' ) ) {
    function um_theme_header_search_type() {
        global $defaults;

        if ( absint( $defaults['um_show_header_search_type'] ) === 1 && class_exists( 'UM' ) ) {
            um_search_form();
        } else {
            get_search_form();
        }
    }
}

/**
 * Remove widgets dashboard.
 */
if ( ! function_exists( 'um_theme_admin_remove_dashboard_widgets' ) ) {
    function um_theme_admin_remove_dashboard_widgets() {
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );

        // Yoast's SEO Plugin Widget.
        remove_meta_box( 'yoast_db_widget', 'dashboard', 'normal' );
    }
}

/**
 * Add resource hints to our Google fonts call.
 *
 * @since 1.0
 *
 * @param array  $urls           URLs to print for resource hints.
 * @param string $relation_type  The relation type the URLs are printed.
 * @return array $urls           URLs to print for resource hints.
 */
if ( ! function_exists( 'um_theme_resource_hints' ) ) {
    function um_theme_resource_hints( $urls, $relation_type ) {
        if ( wp_style_is( 'um-theme-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
            if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '>=' ) ) {
                $urls[] = array(
                    'href' => 'https://fonts.gstatic.com',
                    'crossorigin',
                );
            } else {
                $urls[] = 'https://fonts.gstatic.com';
            }
        }
        return $urls;
    }
}

/**
 * Archive Section Title
 */
if ( ! function_exists( 'um_theme_archive_content_title' ) ) {
    function um_theme_archive_content_title() {
        echo '<h1 class="page-title">';
        the_archive_title();
        echo '</h1>';
    }
}


/**
 * Archive Section Description
 */
if ( ! function_exists( 'um_theme_archive_content_description' ) ) {
    function um_theme_archive_content_description() {
        echo '<div class="taxonomy-description">';
        the_archive_description();
        echo '</div>';
    }
}

/**
 * Page Header Section
 */
if ( ! function_exists( 'um_theme_output_page_header' ) ) {
    function um_theme_output_page_header() {
        echo '<header class="boot-col-md-12 page__header entry-header">';
        echo '<div class="single page-meta">';
        echo '<h1 class="entry-title">';
        the_title();
        echo '</h1>';
        echo '</div>';
        echo '</header>';
    }
}


/**
 * Page Content Section
 */
if ( ! function_exists( 'um_theme_output_page_content' ) ) {
    function um_theme_output_page_content() {
        echo '<div class="entry-content">';
        the_content();

        wp_link_pages( array(
            'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'um-theme' ),
            'after'  => '</div>',
        ) );

        echo '</div>';
    }
}

/**
 * Sticky Sidebar Widgets Opening
 */
if ( ! function_exists( 'um_theme_sticky_sidebar_opening' ) ) {
    function um_theme_sticky_sidebar_opening() {
        global $defaults;
        if ( absint( $defaults['um_theme_sticky_sidebar'] ) === 1 ) {
            echo '<div class="boot-sticky-top">';
        }

    }
}

/**
 * Sticky Sidebar Widgets Closing
 */
if ( ! function_exists( 'um_theme_sticky_sidebar_closing' ) ) {
    function um_theme_sticky_sidebar_closing() {
        global $defaults;
        if ( absint( $defaults['um_theme_sticky_sidebar'] ) === 1 ) {
            echo '</div>';
        }

    }
}

/**
 * Output Header Sticky Class
 */
if ( ! function_exists( 'um_theme_output_header_sticky_class' ) ) {
    function um_theme_output_header_sticky_class() {
        global $defaults;
        if ( absint( $defaults['um_theme_make_header_sticky'] ) === 1 ) {
            echo 'boot-sticky-top';
        }

    }
}

/**
 * Add rel="nofollow" and remove rel="category".
 */
if ( ! function_exists( 'um_theme_modify_category_rel' ) ) {
    function um_theme_modify_category_rel( $text ) {
        $search = array( 'rel="category"', 'rel="category tag"' );
        $text = str_replace( $search, 'rel="nofollow"', $text );

        return $text;
    }
}

/**
 * Add rel="nofollow" and remove rel="tag".
 */
if ( ! function_exists( 'um_theme_modify_tag_rel' ) ) {
    function um_theme_modify_tag_rel( $taglink ) {
        return str_replace( 'rel="tag">', 'rel="nofollow">', $taglink );
    }
}

/**
 * Add image alt tag to post featured image.
 */
if ( ! function_exists( 'um_theme_add_post_featured_image_alt_text' ) ) {
    function um_theme_add_post_featured_image_alt_text( $attr, $attachment = null ) {
        $img_title = trim( strip_tags( $attachment->post_title ) );
        $attr['alt'] = $img_title;

        return $attr;
    }
}

/**
 * Change the Leave a Reply title from h3 to h4.
 */
if ( ! function_exists( 'um_theme_comment_reply_title' ) ) {
    function um_theme_comment_reply_title( $defaults ){
      $defaults['title_reply_before'] = '<h4 id="reply-title" class="comment-reply-title">';
      $defaults['title_reply_after'] = '</h4>';
      return $defaults;
    }
}

/**
 * Change Default Comment Login link to UM Login Page
 */
if ( ! function_exists( 'um_theme_change_login_link' ) ) {
    function um_theme_change_login_link( $defaults ) {
        if ( class_exists( 'UM' ) ) {
            $defaults['must_log_in'] = '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.' ), um_get_core_page( 'login' ) ) . '</p>';
            return $defaults;
        }
    }
}