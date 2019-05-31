jQuery(document).ready(function () {

    jQuery('.um-reviews-avg').um_raty({
        half: true,
        starType: 'i',
        number: function () {
            return jQuery(this).attr('data-number');
        },
        score: function () {
            return jQuery(this).attr('data-score');
        },
        hints: ['1 Star', '2 Star', '3 Star', '4 Star', '5 Star'],
        space: false,
        readOnly: true
    });

    jQuery('.um-reviews-rate').um_raty({
        half: false,
        starType: 'i',
        number: function () {
            return jQuery(this).attr('data-number');
        },
        score: function () {
            return jQuery(this).attr('data-score');
        },
        scoreName: function () {
            return jQuery(this).attr('data-key');
        },
        hints: ['1 Star', '2 Star', '3 Star', '4 Star', '5 Star'],
        space: false
    });
    resizeDoc();
});
jQuery(window).on('resize orientationchange', function () {
    resizeDoc();
});

/**
 * This function works at boot time "jQuery(document).ready()" and at events "resize orientationchange"
 **/
function resizeDoc() {
    var bw = jQuery("body").width();
    /**
     * It puts the "Name" column to the top and in the end
     **/
    if (bw <= 782) {
        jQuery('.post-type-um_review thead tr,.post-type-um_review tbody tr,.post-type-um_review tfoot tr').each(function (i, element) {
            jQuery('.column-review_from', jQuery(element)).before(jQuery('.column-title', jQuery(element)));
        });
    } else {
        jQuery('.post-type-um_review thead tr,.post-type-um_review tbody tr,.post-type-um_review tfoot tr').each(function (i, element) {
            jQuery(".column-review_flag", jQuery(element)).after(jQuery('.column-title', jQuery(element)));
        });
    }

}