jQuery(window).load(function () {

    setTimeout(function () {

        jQuery('.um-reviews-d-p span').each(function () {
            jQuery(this).animate({'width': jQuery(this).attr('data-width') + '%'});
        });

    }, 2000);

});

jQuery(document).ready(function () {

    var enabledButton = function (this_form) {
        var success = 1;
        if (this_form.find('.um-reviews-rate input').val() == '') {
            success = 0;
        } else if (this_form.find('.um-reviews-title input').val().length < 1) {
            success = 0;
        } else if (this_form.find('.um-reviews-content textarea').val().length < 1) {
            success = 0;
        }
        if (success) {
            this_form.find('span.um-reviews-send input.um-button').removeAttr('disabled');
        }
    };

    jQuery('form.um-reviews-form').on('click', '.um-reviews-send .um-button', function (e) {
        e.preventDefault();

        if ( typeof jQuery.fn.um_raty === 'undefined' ) {
            return false;
        }
        var this_form = jQuery(this).closest( 'form.um-reviews-form' );

        if ( this_form.find('.um-reviews-rate input').val() == '' ) {
            this_form.find('.um-field-error').html(um_reviews.add_rating).show();
        } else if (this_form.find('.um-reviews-title input').val().length < 1) {
            this_form.find('.um-field-error').html(um_reviews.provide_title).show();
        } else if (this_form.find('.um-reviews-content textarea').val().length < 1) {
            this_form.find('.um-field-error').html(um_reviews.provide_review_content).show();
        } else {

            this_form.find('.um-field-error').hide().empty();
	        wp.ajax.send({
		        data: this_form.serialize(),
		        success: function (data) {
			        this_form.parents('.um-reviews-item').siblings('.um-reviews-none').hide();

			        this_form.parents('.um-reviews-item').find('.um-reviews-post.review-form').hide();
			        if (this_form.parents('.um-reviews-item').find('.um-reviews-post.review-new').length) {
				        conta = this_form.parents('.um-reviews-item').find('.um-reviews-post.review-new');
			        } else {
				        conta = this_form.parents('.um-reviews-item').find('.um-reviews-post.review-list');
			        }
			        conta.show();
			        conta.find('.um-reviews-title').html('<span>' + data.title + '</span>');
			        conta.find('.um-reviews-content').html(data.content);
			        conta.find('.um-reviews-send').hide();
			        conta.find('.um-reviews-avg').um_raty({
				        half: true,
				        starType: 'i',
				        number: function () {
					        return jQuery(this).attr('data-number');
				        },
				        score: data.rating,
				        hints: ['1 ' + um_reviews.star, '2 ' + um_reviews.star, '3 ' + um_reviews.star, '4 ' + um_reviews.star, '5 ' + um_reviews.star],
				        space: false,
				        readOnly: true
			        });

			        if (data.pending) {
				        conta.find('.um-reviews-note').html(data.pending).css({'display': 'inline-block'}).show();
			        }
                },
                error: function(data) {
	                this_form.find('.um-field-error').html(data).show();
                }
	        });
        }
        return false;
    }).on('input', 'form.um-reviews-form', function () {
        var this_form = jQuery(this);
        enabledButton(this_form);
    }).on('click', '.um-reviews-rate', function () {
        var this_form = jQuery(this).closest('form.um-reviews-form');
        enabledButton(this_form);
    });

    jQuery(document.body).on('click', '.um-reviews-cancel-add', function (e) {
        e.preventDefault();
        jQuery('.um-reviews-prepost').show();
        jQuery(this).parent().hide();
        return false;
    });

    jQuery(document.body).on('click', '.um-reviews-cancel-edit', function (e) {
        e.preventDefault();
        jQuery(this).parents('.um-reviews-item').find('.review-list').show();
        jQuery(this).parent().hide();
        return false;
    });

    jQuery(document.body).on('click', '.um-reviews-edit a', function (e) {
        e.preventDefault();
        var p_this = jQuery(this).parents('.um-reviews-item');
        var fade__ = jQuery(this).parents('.um-reviews-item').find('.um-reviews-post.review-form');
        jQuery(this).parents('.um-reviews-item').find('.um-reviews-post.review-list').hide();

        jQuery('.um-reviews-cancel-edit').parents('.um-reviews-item').not(p_this).find('.review-list').show();
        jQuery('.um-reviews-cancel-edit').parent().hide();

        fade__.show();
        return false;
    });

    jQuery(document.body).on('click', '.um-reviews-prepost', function (e) {
        var fade__ = jQuery(this).parent().find('.um-reviews-post.review-form');
        jQuery(this).hide();
        fade__.show();
    });

    jQuery(document.body).on('click', '.um-reviews-remove a', function (e) {
        e.preventDefault();
        if (jQuery(this).parents('.um-reviews-item').find('.um-reviews-remove-a').length == 0) {
            jQuery(this).hide();
            var text = jQuery(this).attr('data-remove');
            var note = jQuery(this).parents('.um-reviews-item').find('.um-reviews-note');
            note.html(text).css({'display': 'inline-block'}).show();
            note.after('<div class="um-reviews-remove-a"><a href="#">' + um_reviews.remove + '</a>&nbsp;&nbsp; | &nbsp;&nbsp;<a href="#" class="cancel-remove">' + um_reviews.cancel + '</a></div>');
        }
        return false;
    });

    jQuery(document.body).on('click', '.um-reviews-remove-a a:not(.cancel-remove)', function (e) {
        e.preventDefault();
        var review_id = jQuery(this).parents('.um-reviews-item').attr('data-review_id');
        var user_id = jQuery(this).parents('.um-reviews-item').attr('data-user_id');
        jQuery(this).parents('.um-reviews-item').remove();

        wp.ajax.send( 'um_review_trash',{
		    data: {
			    review_id: review_id,
			    user_id: user_id,
			    nonce: um_scripts.nonce
		    },
		    success: function (msg) {
			    //console.log( 'UM Reviews trash return message:',msg );
		    },
		    error: function (e) {
			    console.log('UM Reviews script error:', e);
		    }
	    });
        return false;
    });

    jQuery(document.body).on('click', '.um-reviews-remove-a a.cancel-remove', function (e) {
        e.preventDefault();
        jQuery(this).parents('.um-reviews-item').find('.um-reviews-remove a').show();
        jQuery(this).parents('.um-reviews-item').find('.um-reviews-note').empty().hide();
        jQuery(this).parent().remove();
        return false;
    });

    jQuery(document.body).on('click', '.um-reviews-flag a', function (e) {
        e.preventDefault();
        var flag = jQuery(this).parent();
        var item = jQuery(this).parents('.um-reviews-item');
        var review_id = jQuery(this).parents('.um-reviews-item').attr('data-review_id');

	    wp.ajax.send( 'um_review_flag', {
		    data: {
			    review_id: review_id,
			    nonce: um_scripts.nonce
		    },
		    success: function (data) {

			    item.find('.um-reviews-note').html(data.response).css({'display': 'inline-block'}).show();
			    flag.hide();

		    }
	    });
        return false;
    });

    jQuery('.um-reviews-avg').um_raty({
        half: true,
        starType: 'i',
        number: function () {
            return jQuery(this).attr('data-number');
        },
        score: function () {
            return jQuery(this).attr('data-score');
        },
        hints: ['1 ' + um_reviews.star, '2 ' + um_reviews.star, '3 ' + um_reviews.star, '4 ' + um_reviews.star, '5 ' + um_reviews.star],
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
        hints: ['1 ' + um_reviews.star, '2 ' + um_reviews.star, '3 ' + um_reviews.star, '4 ' + um_reviews.star, '5 ' + um_reviews.star],
        space: false
    });

});
