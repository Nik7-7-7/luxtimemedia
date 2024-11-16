mailster = (function (mailster, $, window, document) {
	'use strict';

	var $base = $('#mailster-health'),
		runbtn = $base.find('.button-primary'),
		id;

	$base.on('submit', '.health_form', initTest);

	function loader(enable) {
		// TODO: implement later
		//$loader.css('visibility', enable ? 'visible' : 'hidden');
	}

	function status(status) {
		mailster.warning(status);
	}
	function initTest() {
		loader(true);
		status('sending');
		runbtn.prop('disabled', true);

		$('.precheck-results').slideDown();
		$('.precheck-result').html('');

		$('.precheck-status-icon').html('');
		$('summary')
			.addClass('loading')
			.removeClass('is-error is-warning is-notice is-success');

		mailster.util.ajax(
			'health_check',
			function (response) {
				if (response.success) {
					id = response.data.id;
					setTimeout(function () {
						status('checking');
						checkTest(1);
					}, 3000);
				} else {
					error(response.data.msg);
					loader(false);
					runbtn.prop('disabled', false);
				}
			},
			function (jqXHR, textStatus, errorThrown) {
				loader(false);
				runbtn.prop('disabled', false);
			}
		);

		return false;
	}

	function error(msg) {
		var box = $('<div class="error"><p><strong>' + msg + '</strong></p></div>')
			.hide()
			.appendTo($('.error-msg'))
			.slideDown(200)
			.delay(200)
			.fadeIn()
			.delay(8000)
			.fadeTo(200, 0)
			.delay(1500)
			.slideUp(200, function () {
				box.remove();
			});
		mailster.error(msg);
	}
	function checkTest(tries) {
		if (tries > 10) {
			error('email not sent');
			loader(false);
			runbtn.prop('disabled', false);
			return;
		}

		mailster.util.ajax(
			'precheck',
			{
				id: id,
			},
			function (response) {
				if (response.success) {
					if (!response.data.ready) {
						setTimeout(function () {
							checkTest(++tries);
						}, 3000);
					} else {
						status('collecting');
						$('.precheck-status-icon').html(
							mailster.util.sprintf('%s of 100', 100)
						);

						$.when
							.apply($, [
								getResult('blocklist'),
								getResult('authentication', 'tests/spf'),
								getResult('authentication', 'tests/dkim'),
								getResult('authentication', 'tests/dmarc'),
								getResult('authentication', 'tests/rdns'),
								getResult('authentication', 'tests/mx'),
								getResult('authentication', 'tests/a'),
							])
							.done(function (r) {
								status('finished');
								loader(false);
								runbtn.prop('disabled', false);
								$('.get-help').show();
							});
					}
				}
				if (response.data.error) {
					error(response.data.error);
					loader(false);
					runbtn.prop('disabled', false);
				}
			},
			function (jqXHR, textStatus, errorThrown) {
				loader(false);
				runbtn.prop('disabled', false);
			}
		);
	}

	function getResult(part, endpoint) {
		var base = $('#precheck-' + part),
			children = base.find('details'),
			child_part,
			promises = [];

		if (children.length) {
			base.find('summary').eq(0).removeAttr('class').addClass('loading');
			for (var i = 0; i < children.length; i++) {
				child_part = children[i].id.replace('precheck-', '');
				if (child_part) {
					endpoint = 'tests/' + child_part;
					promises.push(getEndpoint(child_part, endpoint));
				}
			}
		} else {
			if (!endpoint) endpoint = part;
			promises.push(getEndpoint(part, endpoint));
		}

		return $.when.apply($, promises).done(function () {
			var s,
				statuses = {
					error: 0,
					warning: 0,
					notice: 0,
					success: 0,
				};
			if (typeof arguments[1] != 'string') {
				for (i in arguments) {
					arguments[i] && statuses[arguments[i][0].status]++;
				}
				if (statuses.error) {
					s = 'error';
				} else if (statuses.warning) {
					s = 'warning';
				} else if (statuses.notice) {
					s = 'notice';
				} else {
					s = 'success';
				}
				$('#precheck-' + part)
					.find('summary')
					.eq(0)
					.removeClass('loading')
					.addClass('loaded is-' + s);
			}
		});
	}

	function getEndpoint(part, endpoint) {
		var base = $('#precheck-' + part),
			summary = base
				.find('summary')
				.eq(0)
				.removeAttr('class')
				.addClass('loading'),
			body = base.find('.precheck-result');

		return mailster.util.ajax(
			'precheck_result',
			{
				id: id,
				endpoint: endpoint,
			},
			function (response) {
				if (response.success) {
					summary
						.removeClass('loading')
						.addClass('loaded is-' + response.data.status);
					if ('error' == response.data.status) {
						//base.prop('open', true);
					}
					//summary.find('.precheck-penality').html(response.data.penalty);
					$('.precheck-status-icon').html(
						mailster.util.sprintf('%s of 100', response.data.points)
					);
					body.html(response.data.html);
				}

				if (response.data.error) {
					error(response.data.error);
					loader(false);
					runbtn.prop('disabled', false);
				}
			},
			function (jqXHR, textStatus, errorThrown) {
				loader(false);
				runbtn.prop('disabled', false);
			}
		);
	}

	return mailster;
})(mailster || {}, jQuery, window, document);
