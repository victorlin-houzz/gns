jQuery(document).ready( function() {

	/**
	 * Join Group
	 */
	jQuery(document).on('click','.um-groups-btn-join',function(){
		var me = jQuery(this);
		var group_id = me.data('group_id');
		var wrap_actions = me.parents('div.actions');
		jQuery.ajax({ 
            method: 'POST',
            url: wp.ajax.settings.url,
            type: 'post',
            data: { action: 'um_groups_join_group', group_id: group_id, nonce: um_scripts.nonce },
	    }).done( function( data ){
	    		if( data.status == true ){
	    			me.addClass('um-groups-btn-leave');
	    			me.removeClass('um-groups-btn-join');
	    			me.text( data.labels.leave );
	    			me.addClass('um-groups-btn-join-request');
	    			if( data.privacy == 'public' ){
	    				wrap_actions.find('ul').find('li.count-members').find('span').text( data.members );
	    				me.addClass('um-groups-has-joined');
	    			}else if( data.privacy == 'hidden' ){
	    				me.hide();
	    			}
	    		}


	    }).error( function( error ){
				console.log('join group error', error );
		});
	});

	/**
	 * Leave Group
	 */
	jQuery(document).on('click','.um-groups-btn-leave',function(){
		var me = jQuery(this);
		var group_id = me.data('group_id');
		var wrap_actions = me.parents('div.actions');
		jQuery.ajax({ 
	        method: 'POST',
	        url: wp.ajax.settings.url,
	        type: 'post',
	        data: { action: 'um_groups_leave_group', group_id: group_id, nonce: um_scripts.nonce },
	    }).done( function( data ){
	    		if( data.status ){
	    			me.addClass('um-groups-btn-join');
	    			me.removeClass('um-groups-btn-leave');
	    			me.removeClass('um-groups-btn-join-request');
	    			me.text( data.labels.join );
	    			me.attr('data-groups-button-hover', data.labels.hover );
	    			if( data.privacy == 'public' || data.privacy == 'private' ){
	    				wrap_actions.find('ul').find('li.count-members').find('span').text( data.members );
	    			}

	    			if( data.privacy == 'hidden' || data.privacy == 'private' ){
	    				window.location.reload();
	    			}

	    		}
	    }).error( function( error ){
				console.log('leave group error', error );
		});
	})

	/**
	 * Switch button text
	 */
	jQuery(document).on({
	    mouseenter: function () {
		    var me = jQuery(this);
		    var label = me.attr('data-groups-button-hover');
		    if( label ){
			    me.text( label );
			}
		    me.addClass('um-groups-btn-leave');
	   },
	    mouseleave: function () {
	        var me = jQuery(this);
			var label = me.attr('data-groups-button-default');
		    if( label ){
			    me.text( label );
			}
		 	me.removeClass('um-groups-btn-leave');
		}
	}, '.um-groups-single-button a.um-button:not(.um-groups-btn-join,.um-groups-has-joined)'); 

	/**
	 * Swap invite button label and icon
	 */
	jQuery(document).on({
	    mouseenter: function () {
	        var me = jQuery(this);
		     	me.removeClass('disabled');
	        	me.html('<span class="um-faicon-paper-plane-o"></span> '+um_scripts.groups_settings.labels.resend);
			
		},
	    mouseleave: function () {
	        var me = jQuery(this);
	         	me.addClass('disabled');
	        	me.html('<span class="um-faicon-check"></span> '+um_scripts.groups_settings.labels.invited);
			
		}
	}, '#um-group-buttons .um-group-button[data-action-key="resend_invite"]:not(.um-groups-has-invited)'); 

	/**
	 * Show/Hide role and status options
	 */
	jQuery(document).on({
	    mouseenter: function () {
	        var tr = jQuery(this);
			tr.find('td').find('select').show();
			tr.find('td').find('span.label').hide();
		},
	    mouseleave: function () {
	        var tr = jQuery(this);
			tr.find('td').find('select').hide();
			tr.find('td').find('span.label').show();
		}
	}, 'table#um_groups_manage_members tbody tr'); 

	/**
	 * Redirect to default tab
	 */
	function um_groups_default_tab(){
		jQuery('ul.um-groups-single-tabs li:first a').trigger('click');
	}

	jQuery('ul.um-groups-single-tabs li:first a').on('click',function(){
		window.location.href = jQuery(this).attr('href');
	});

	/**
	 * Confirm Join Invite
	 */
	jQuery(document.body).on('click','.um-groups-confirm-invite',function() {
		var me = jQuery(this);
		var user_id = me.attr('data-user_id');
		var group_id = jQuery('input[name="group_id"]').val();

		jQuery.ajax({ 
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: { action: 'um_groups_confirm_invite', group_id: group_id, user_id: user_id, self: true, nonce: um_scripts.nonce }
	    }).done( function( data ){
	    	window.location.reload();
		}).error( function( e ){
			console.log( e );
		});
	});

	jQuery(document.body).on('click','.um-groups-confirm-invite-in-list',function() {
		var me = jQuery(this);
		var user_id = me.attr('data-user_id');
		var group_id = me.attr('data-group');

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: { action: 'um_groups_confirm_invite', group_id: group_id, user_id: user_id, self: true, nonce: um_scripts.nonce }
		}).done( function( data ){
			window.location.reload();
		}).error( function( e ){
			console.log( e );
		});
	});

	/**
	 * Ignore Join Invite
	 */
	jQuery(document.body).on('click','.um-groups-ignore-invite',function() {
		var me = jQuery(this);
		var user_id = me.attr('data-user_id');
		var group_id = jQuery('input[name="group_id"]').val();
		
		jQuery.ajax({ 
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: { action: 'um_groups_ignore_invite', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce }
		}).done( function( data ){
			window.location.reload();
		}).error( function( e ){
			console.log( e );
		});
	});

	jQuery(document.body).on('click','.um-groups-ignore-invite-in-list',function() {
		var me = jQuery(this);
		var user_id = me.attr('data-user_id');
		var group_id = me.attr('data-group');

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: { action: 'um_groups_ignore_invite', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce }
		}).done( function( data ){
			window.location.reload();
		}).error( function( e ){
			console.log( e );
		});
	});

	/**
	 * Show more buttons
	 */
	jQuery(document).on('click','.um-group-buttons a.um-group-button-more',function(){
		var me = jQuery(this);

		jQuery('.um-group-buttons a.um-group-button-more.active').removeClass('active');
			
		var group_buttons = me.parent('.um-group-buttons').find('ul[class=um-group-buttons-more]');
		if( group_buttons.is(':visible') ){
			group_buttons.hide();
		}else{
			jQuery('ul[class=um-group-buttons-more]').hide();
			group_buttons.show();
			me.addClass('active');
		}
		return false;
	});

	/**
	 * Group User Actions
	 */
	jQuery( document.body ).on( 'click', '.um-group-buttons a[data-action-key]', function() {
		var me = jQuery(this);
		var parent = me.parents('.um-groups-user-wrap');
		var action = me.data('action-key');
		var group_id = parent.data("group-id");
		var user_id = parent.data("group-uid");
		var members_count = jQuery('.um-groups-single').find('.um-group-members-count').find('.count');

		switch( action ) {
			case 'approve':
			
				jQuery.ajax({ 
					method: 'POST',
					url: wp.ajax.settings.url,
					type: 'post',
					data: {
						action: 'um_groups_approve_user',
						group_id: group_id,
						user_id: user_id,
						nonce: um_scripts.nonce
					}
				}).done( function( data ){

					if ( data.status == true ) {
						parent.fadeOut();
						um_groups_update_tab_count()
					}

				}).error( function( error ){
					console.log('approve member error', error );
				});

				break;

			case 'invite':

				if( ! me.hasClass('um-groups-has-invited') ){
					jQuery.ajax({ 
			            method: 'POST',
			            url: wp.ajax.settings.url,
			            type: 'post',
						data: { action: 'um_groups_send_invitation_mail', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce },
				    }).done( function( data ){
				    
				    	if( data.found ){
				        	me.addClass('disabled');
				        	me.addClass('um-groups-has-invited');
							me.html('<span class="um-faicon-check"></span> '+um_scripts.groups_settings.labels.invited);

						}
					
					}).error( function( error ){
							console.log('send invite error', error );
					});
				}

			break;

			case 'resend_invite':

				if( ! me.hasClass('um-groups-has-invited') ){
					jQuery.ajax({ 
			            method: 'POST',
			            url: wp.ajax.settings.url,
			            type: 'post',
			            data: { action: 'um_groups_send_invitation_mail', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce },
				    }).done( function( data ){
				    	
				    	if( data.found ){
				        	me.addClass('disabled');
				        	me.addClass('um-groups-has-invited');
							me.html('<span class="um-faicon-check"></span> '+um_scripts.groups_settings.labels.resent_invite);

						}
					
					}).error( function( error ){
							console.log('resend invite error', error );
					});
				}

			break;

			case 'make-admin':
			case 'make-moderator':
			case 'make-member':

				var role = action.replace('make-','');
				var data = { action: 'um_groups_change_member_role',group_id: group_id, user_id: user_id, role: role, nonce: um_scripts.nonce };

				jQuery.ajax({ 
		            method: 'POST',
		            url: wp.ajax.settings.url,
		            type: 'post',
		            data: data,
			    }).done( function( data ){

			    		if( data.found ){
			    			me.html( data.menus[ data.previous_role ] );
			    			me.attr('data-action-key', 'make-'+data.previous_role );

				    	}

				}).error( function( error ){
						console.log('change member role error', error,  data );
				});

			break;

			case 'remove-from-group':

				var result = confirm( um_scripts.groups_settings.labels.confirm_expel );
							
				if( result ){

					jQuery.ajax({ 
			    		method: 'POST',
			    		url: wp.ajax.settings.url,
			    		type: 'post',
			            data: { action: 'um_groups_delete_member', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce },
			        }).done( function( data ){

			        	if( data.found ){
			        		
			        		parent.fadeOut();
							
							um_groups_update_tab_count();

						}

					})

				}

			break;

			case 'remove-self-from-group':

				var result = confirm( um_scripts.groups_settings.labels.confirm_remove_self );
							
				if( result ){

					jQuery.ajax({ 
			    		method: 'POST',
			    		url: wp.ajax.settings.url,
			    		type: 'post',
			            data: { action: 'um_groups_delete_member', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce },
			        }).done( function( data ){

			        	if( data.found ){
			        		
			        		parent.fadeOut();
							
							um_groups_update_tab_count();

						}

					})

				}

			break;

			case 'reject':

				jQuery.ajax({ 
					method: 'POST',
					url: wp.ajax.settings.url,
					type: 'post',
					data: { action: 'um_groups_reject_user', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce },
			    }).done( function( data ){

					parent.fadeOut();
					
					um_groups_update_tab_count();
				
				});

			break;

			case 'block':

				jQuery.ajax({ 
					method: 'POST',
					url: wp.ajax.settings.url,
					type: 'post',
					data: { action: 'um_groups_block_user', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce },
			    }).done( function( data ){

					parent.fadeOut();
					
					um_groups_update_tab_count();
				
				});

			break;

			case 'unblock':

				jQuery.ajax({ 
					method: 'POST',
					url: wp.ajax.settings.url,
					type: 'post',
					data: { action: 'um_groups_unblock_user', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce },
			    }).done( function( data ){

					parent.fadeOut();
					
					um_groups_update_tab_count();
				
				});

			break;

		}

		return false;
	});
	

	/**
	 * Updates tab requests count
	 */
	function um_groups_update_tab_count(){
		var requested_users_count = jQuery('li[class^=um-groups-tab-slug_].active').find('.count');
		if ( requested_users_count ) {
			var c = parseInt( requested_users_count.text() ) - 1;
			requested_users_count.text( c );

			if ( c <= 0 ) {
				window.location.reload();
			}
		}
	}

	/**
	 * Load More Users
	 */
	jQuery(document).on('click','a.um-groups-load-more',function(){
		var load_more = jQuery(this).data('load-more');

		jQuery(this).text('...');

		var me = jQuery(this);
		var user_id = me.data('user_id');
		var group_id = me.data('group-id');
		var load_type = me.data('load-more');
		var offset = me.data('users-offset');
		var data = { action: 'um_groups_load_more_users', group_id: group_id, user_id: user_id, status: load_type, offset: offset, nonce: um_scripts.nonce };
		var keyword = jQuery('#um-groups-users-search-form input[type=text]').val();
		if( keyword ){
			data.keyword = keyword;
		}
		
		jQuery.ajax({ 
            method: 'POST',
            url: wp.ajax.settings.url,
            type: 'json',
			data: data,
	    }).done( function( data ){

	    	setTimeout( function(){
		    	me.parent('div').after( data.html );
		    	me.parent('div').remove();
		    },1000);
		
		}).error( function( e ){

			console.log( e );

		});

		return false;

	});

	/**
	 * Search Users
	 */
	jQuery(document).on('keypress','#um-groups-users-search-form input[type=text]', function( e ) {
		var me = jQuery(this);
		if( e.which == 13 ){
			me.parent('form').trigger('submit');

			return false;

		}
	});
	jQuery(document).on('change','#um-groups-users-search-form select', function( e ) {
		var me = jQuery(this);
		me.parent('form').trigger('submit');
		return false;
	});

	/**
	 * Search Users to Invites
	 */
	jQuery(document).on('submit','#um-groups-users-search-form',function( e ){
		var me = jQuery(this);
		var parent = jQuery('#um-groups-users-search-form');
		var group_id = parent.data('group-id');
		var load_type = parent.data('load-more');
		var keyword = me.find('input[name=search-user]').val();
		var wrap = jQuery('.um-groups-wrapper');
		var custom_filter = [];

		me.find('input').not('input[name=search-user], input[type=hidden]').each( function () {
			var key = jQuery(this).attr('name');
			var value = jQuery(this).val();
			if( value ) {
				custom_filter.push({"key": key, "value": value});
			}
		});
		me.find('select').each( function () {
			var key = jQuery(this).attr('name');
			var value = jQuery(this).val();
			if( value ){
				custom_filter.push({"key": key, "value": value});
			}
		});

		wrap.empty();
		wrap.text('...');

		jQuery.ajax({ 
            method: 'POST',
            url: wp.ajax.settings.url,
            type: 'json',
			data: { action: 'um_groups_load_more_users', group_id: group_id, status: load_type, keyword: keyword, offset: 0, filter: custom_filter, nonce: um_scripts.nonce },
		}).done( function( data ){

			setTimeout( function(){
				wrap.empty();
				wrap.append( data.html );
			},1000);
			
		}).error( function( e ){

			console.log( e );

		});
	

		

		return false;
	});

	/**
	 * Groups directory pagination
	 */
	jQuery(document).on('click','.um-groups-lazy-load',function(){

		var me = jQuery(this);
		var data = { action: 'um_groups_load_more_groups',settings: me.data("groups-pagi-settings") , nonce: um_scripts.nonce };
		var parent = me.parent('.um-groups-list-pagination');
		var page = parseInt( me.attr("data-groups-page") ) + 1;
		
		data.settings.page = page;
		
		me.text('...');

		jQuery.ajax({ 
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'json',
			data: data,
		}).done( function( data ){
			
			if( data.found.total_groups == 0 ){
				me.text( me.data('no-more-groups-text') );
				parent.remove();
			}else{
				me.text( me.data('load-more-text') );
				me.attr("data-groups-page", page );
				parent.before( data.html );
			}

			if( page >= data.found.total_pages ){
				parent.remove();
			}

		}).error( function( e ){
			
			me.text( um_scripts.groups_settings.labels.went_wrong );

		});

		return false;
	})


}); // end jQuery(document).ready
