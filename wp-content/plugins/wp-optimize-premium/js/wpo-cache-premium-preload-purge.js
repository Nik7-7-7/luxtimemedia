jQuery(function($) {
	var send_command = wp_optimize.send_command;

	$('.row-actions').on('click', '.wpo_preload_cache a', function(e) {
		e.preventDefault();
		var data = {
			'post_id': $(this).data('post_id')
		};
		var preload_link = this;
		$(preload_link).prop('disabled', true);
		send_command('single_page_cache_preload', data, function(response) {
			if (response) {
				modal_message(response.message, $.unblockUI);
				setTimeout(function() {
					if (response.success) {
						$(preload_link).text(wp_optimize_admin.purge);
						$(preload_link).parent().removeClass('wpo_preload_cache').addClass('wpo_purge_cache');
					}
					$.unblockUI();
					$(preload_link).prop('disabled', false);
				}, 1000);
			}
		});
	});

	$('.row-actions').on('click', '.wpo_purge_cache a', function(e) {
		e.preventDefault();
		var data = {
			'post_id': $(this).data('post_id')
		};
		var purge_link = this;
		$(purge_link).prop('disabled', true);
		send_command('single_page_cache_purge', data, function(response) {
			if (response) {
				modal_message(response.message, $.unblockUI);
				setTimeout(function() {
					if (response.success) {
						$(purge_link).text(wp_optimize_admin.preload);
						$(purge_link).parent().removeClass('wpo_purge_cache').addClass('wpo_preload_cache');
					}
					$.unblockUI();
					$(purge_link).prop('disabled', false);
				}, 1000);
			}
		});
	});

	function modal_message(message, callback) {

		$.blockUI({
			message: message,
			onOverlayClick: callback,
			baseZ: 160001,
			css: {
				width: '400px',
				padding: '20px',
				cursor: 'pointer'
			}
		});
	}
});