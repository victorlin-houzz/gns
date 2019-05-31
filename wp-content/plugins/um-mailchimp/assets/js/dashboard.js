var um_mailchimp_create_template = function( id, data ) {
	var template = wp.template( id ),
		key = 'um_mailchimp_template_' + (new Date()).getMilliseconds();

	if( typeof window.um_mailchimp_dashboard_data === 'undefined' ) {
		window.um_mailchimp_dashboard_data = {};
	}

	window.um_mailchimp_dashboard_data[ key ] = {
		template : template,
		data : data
	};
	return key;
};

var um_mailchimp_render_template = function( key, $object ) {
	if( typeof window.um_mailchimp_dashboard_data[ key ] !== 'undefined' ) {
		var content = window.um_mailchimp_dashboard_data[ key ].template( window.um_mailchimp_dashboard_data[key].data );
		if( typeof $object !== 'undefined' ) {
			window.um_mailchimp_dashboard_data[key].object = $object;
		}
		window.um_mailchimp_dashboard_data[key].object.html( content );
		return true;
	}
	return false;
};

var um_mailchimp_update_template = function( key, data ) {
	data = data || {};
	if( typeof window.um_mailchimp_dashboard_data[ key ] !== 'undefined' ) {
		for( var index in data ) {
			window.um_mailchimp_dashboard_data[ key ]['data'][ index ] = data[ index ];
		}

		return um_mailchimp_render_template( key );
	}
	return false;
};

window.um_sync_now = um_mailchimp_create_template( 'um-mailchimp-sync-metabox', um_mailchimp_data );
um_mailchimp_render_template( window.um_sync_now, jQuery('#um-mailchimp-sync-metabox-wrapper') );

jQuery(document).on('click', '#btn_um_mailchimp_sync_now:not(.disabled)', function(e) {
	e.preventDefault();

	var list = jQuery('.um_mailchimp_sync_list').val();

	um_mailchimp_update_template( window.um_sync_now, {
		button_disabled : true, // disable all progress buttons to prevent conflicts
		message : um_mailchimp_data.labels.sync_message,
		loading : true,
		list : list
	} );

	var lists = [];
	if( list ) {
		lists.push( list );
	} else {
		jQuery('.um_mailchimp_sync_list option').each(function() {
			if( jQuery(this).val() ) {
				lists.push( jQuery(this).val() );
			}
		});
	}

	var index = 0;
	sync_process( lists[index] );

	window.sync_key = window.sync_key || '';

	function sync_process( list_id ) {
		jQuery.post(wp.ajax.settings.url, {
			action : 'um_mailchimp_sync_now',
			list : list_id,
			index : index,
			total : lists.length,
			key : window.sync_key,
			nonce: um_admin_scripts.nonce
		}, function (json) {
			if( index + 1 < lists.length ) {
				setTimeout(function () {
					index++;
					sync_process( lists[ index ] );
				}, 1000);
			}

			if (json.success) {
				window.sync_key = json.data.key;

				um_mailchimp_update_template(window.um_sync_now, {
					message: json.data.message,
					loading: index + 1 < lists.length
				});
				if( index + 1 === lists.length ) {
					setTimeout(function () {
						window.location = um_mailchimp_data.current_url
					}, 1000);
				}
			} else {
				alert(json.data);
			}
		}).fail(function (xhr, status, error) {
			alert(status + ' ' + error);
		});
	}
});

window.um_scan_now = um_mailchimp_create_template( 'um-mailchimp-subscribe-metabox', um_mailchimp_data );
um_mailchimp_render_template( window.um_scan_now, jQuery('#um-mailchimp-subscribe-metabox-wrapper') );

jQuery(document).on('click', '#btn_um_mailchimp_scan_now:not(.disabled)', function(e) {
	e.preventDefault();
	var role = jQuery('.um_mailchimp_user_role').val(),
		status = jQuery('.um_mailchimp_user_status').val();

	um_mailchimp_update_template( window.um_scan_now, {
		button_disabled : true, // disable all progress buttons to prevent conflicts
		message : um_mailchimp_data.labels.scan_message,
		loading : true,
		role : role,
		status : status
	} );

	jQuery.post( wp.ajax.settings.url, {
		action : 'um_mailchimp_scan_now',
		role : role,
		status : status,
		nonce: um_admin_scripts.nonce
	}, function (json) {
		if( json.success ) {
			window.bulk_action = {
				key : json.data.key,
				role : role,
				status : status
			};
			um_mailchimp_update_template( window.um_scan_now, {
				message : json.data.scan_total_message,
				loading : false,
				step : 2,
				button_disabled : false
			} );
		} else {
			alert(json.data);
		}
	}).fail(function(xhr, status, error) {
		alert( status + ' ' + error );
	});
});

jQuery(document).on('click', '#btn_um_mailchimp_bulk_subscribe:not(.disabled)', function(e) {
	e.preventDefault();

	var lists = [];
	if( jQuery('.um_mailchimp_list').val() ) {
		lists.push( jQuery('.um_mailchimp_list').val() );
	} else {
		jQuery('.um_mailchimp_list option').each(function() {
			if( jQuery(this).val() ) {
				lists.push( jQuery(this).val() );
			}
		});
	}

	um_mailchimp_update_template( window.um_scan_now, {
		button_disabled : true, // disable all progress buttons to prevent conflicts
		message : um_mailchimp_data.labels.start_bulk_subscribe_process,
		loading : true,
		internal_lists : jQuery('.um_mailchimp_list').val()
	} );

	var index = 0;
	window.batch_ids = [];
	subscribe_process( lists[index] );

	function subscribe_process( list_id ) {
		jQuery.post( wp.ajax.settings.url, {
			action : 'um_mailchimp_bulk_subscribe',
			list : list_id,
			index : index,
			total : lists.length,
			key : window.bulk_action.key,
			role : window.bulk_action.role,
			status : window.bulk_action.status,
			nonce: um_admin_scripts.nonce
		}, function (json) {
			if( index + 1 < lists.length ) {
				setTimeout(function () {
					index++;
					subscribe_process( lists[ index ] );
				}, 1000);
			}

			if (json.success) {
				window.batch_ids.push( json.data.batch_id );
				um_mailchimp_update_template(window.um_scan_now, {
					message: json.data.message,
					loading: index + 1 < lists.length
				});
				if( index + 1 === lists.length ) {
					setTimeout(function () {
						window.location = um_mailchimp_data.current_url
					}, 1000);
				}
			} else {
				alert(json.data);
			}
		}).fail(function (xhr, status, error) {
			alert(status + ' ' + error);
		});
	}
});