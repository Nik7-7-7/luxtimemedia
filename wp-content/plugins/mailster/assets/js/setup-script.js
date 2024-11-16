mailster = (function (mailster, $, window, document) {
	'use strict';

	var steps = $('.mailster-setup-step'),
		currentStep,
		currentID,
		status = $('.status'),
		spinner = $('.spinner'),
		startStep = $('#step_start'),
		hash = location.hash.substring(1),
		tinymce = window.tinymce || false,
		templatesLoaded = false;

	if (hash && $('#step_' + hash).length) {
		startStep.removeClass('active');
		currentStep = $('#step_' + hash);
	} else {
		currentStep = startStep;
	}

	currentID = currentStep.attr('id').replace(/^step_/, '');

	step(currentID);

	$('form.mailster-setup-step-form').on('submit', function () {
		$('.next-step:visible').hide();
		return false;
	});

	$('#mailster-setup')
		.on('click', '.validation-skip-step', function () {
			return confirm(mailster.l10n.setup.skip_validation);
		})
		.on('click', '.next-step', function () {
			if ($(this).hasClass('disabled')) return false;

			save();
		})
		.on('click', '.load-language', function () {
			status.html(mailster.l10n.setup.load_language);
			spinner.css('visibility', 'visible');
			mailster.util.ajax('load_language', function (response) {
				spinner.css('visibility', 'hidden');
				status.html(response.data.html);
				if (response.success) {
					location.reload();
				}
			});

			return false;
		})
		.on('click', '.quick-install', function () {
			var _this = $(this),
				section = _this.closest('section'),
				name = section.data('name'),
				method = section.data('method'),
				plugin = section.data('plugin');

			if (_this.hasClass('loading')) return false;
			_this.addClass('loading');
			_this.prop('disabled', true);

			quickInstall(method, plugin, 'install', null, function () {
				$('section.current').removeClass('current');
				_this.closest('section').addClass('current');

				$('#deliverymethod').val(method);
				$('#step_delivery')
					.find('.next-step')
					.html(sprintf(mailster.l10n.setup.use_deliverymethod, name));
				_this.removeClass('loading');
				$('#step_delivery').find('.quick-install').removeClass('disabled');
				_this.addClass('disabled');
				_this.prop('disabled', false);
				save();
			});
		})
		.on('click', '.save-delivery', function () {
			var _this = $(this),
				form = _this.closest('form'),
				data = form.serialize(),
				section = _this.closest('section'),
				name = section.data('name'),
				method = section.data('method'),
				plugin = section.data('plugin');

			if (section.hasClass('loading')) return false;

			section.addClass('loading');
			_this.addClass('loading');

			save(function () {
				quickInstall(method, plugin, 'install', null, function (response) {
					section.removeClass('loading');
					_this.removeClass('loading');
				});
			});
		})
		.on('click', '.send-test', function () {
			var _this = $(this),
				section = _this.closest('section'),
				to = $('input[name="mailster_options[from]"]').val(),
				formdata = _this.closest('form').serialize();

			if (section.hasClass('loading')) return false;
			section.addClass('loading');
			_this.addClass('loading');
			_this.prop('disabled', true);

			mailster.util.ajax(
				'send_test',
				{
					test: true,
					formdata: formdata,
					basic: true,
					to: to,
				},
				function (response) {
					if (response.data.log)
						response.success
							? mailster.log(response.data.log)
							: mailster.log(response.data.log, 'error');

					section.removeClass('loading');
					_this.removeClass('loading');

					_this.prop('disabled', false);
					var msg = $('<span>' + response.data.msg + '</span>')
						.hide()
						.appendTo(section.find('.deliverystatus'))
						.slideDown(200)
						.delay(200)
						.fadeIn()
						.delay(4000)
						.fadeTo(200, 0)
						.delay(200)
						.slideUp(200, function () {
							msg.remove();
						});
				},
				function (jqXHR, textStatus, errorThrown) {
					section.removeClass('loading');
					_this.removeClass('loading');
					_this.prop('disabled', false);
					var msg = $(
						'<span>' +
							textStatus +
							' ' +
							jqXHR.status +
							': ' +
							errorThrown +
							'</span>'
					)
						.hide()
						.appendTo(section.find('.deliverystatus'))
						.slideDown(200)
						.delay(200)
						.fadeIn()
						.delay(4000)
						.fadeTo(200, 0)
						.delay(200)
						.slideUp(200, function () {
							msg.remove();
						});
				}
			);
		})
		.on('click', '.edit-slug', function () {
			$(this)
				.parent()
				.parent()
				.find('span')
				.hide()
				.filter('.edit-slug-area')
				.show()
				.find('input')
				.focus()
				.select();
		})
		.on('click', '.action-buttons a.edit-homepage', addFocus)
		.on('click', '.template', function () {
			if (!$(this).hasClass('is-locked')) {
				$('.template.active').removeClass('active');
				$(this).addClass('active');
				$('#default_template').val($(this).data('slug'));
			}
		})
		.on('click', '.upgrade-plan', function () {
			var plan_id = $(this).data('plan');
			addCheckout(function (handler) {
				handler.open({
					checkout_style: 'next',
					plan_id: plan_id,
					purchaseCompleted: function (response) {
						console.log('purchaseCompleted', response);
						query_templates(true);
					},
				});
			});
		});

	function addCheckout(cb) {
		if (window.FS) {
			var handler = FS.Checkout.configure(mailster_freemius);
			cb && cb(handler);
			return;
		}
		$.getScript(
			'https://checkout.freemius.com/checkout.min.js',
			function (data, textStatus, jqxhr) {
				var handler = FS.Checkout.configure(mailster_freemius);
				cb && cb(handler);
			}
		);
	}

	function addFocus() {
		mailster.$.window.on('focus', reloadOnFocus);
	}

	function reloadOnFocus() {
		mailster.$.window.off('focus', reloadOnFocus);
		mailster.$.window.off('blur', addFocus);
		mailster.$.window.one('blur', addFocus);
		$('.mailster-homepage-previews')
			.find('iframe')
			.each(function () {
				var _this = $(this);
				var url = _this.attr('src');
				_this.attr('src', url);
			});
	}

	function save(cb) {
		var data = currentStep.find('form').serialize();

		mailster.util.ajax(
			'wizard_save',
			{ id: currentID, data: data },
			function (response) {
				response.success && cb && cb(response);
			}
		);
	}

	mailster.$.window.on('hashchange', function () {
		var id = location.hash.substr(1) || 'start',
			current = $('.mailster-setup-steps-nav').find("a[href='#" + id + "']");

		if (current.length) {
			step(id);
			current.parent().parent().find('a').removeClass('next prev current');
			current.parent().prevAll().find('a').addClass('prev');
			current.addClass('current');
			if (tinymce && tinymce.activeEditor)
				tinymce.activeEditor.theme.resizeTo('100%', 200);
		}

		switch (id) {
			case 'start':
				break;
			case 'templates':
				if (!templatesLoaded) {
					query_templates();
					templatesLoaded = true;
				}
				break;
			case 'finish':
				save();

				break;
		}
	});

	mailster.events.push('documentReady', function () {
		mailster.$.window.trigger('hashchange');
	});

	function step(id) {
		var step = $('#step_' + id);

		if (step.length) {
			currentStep.removeClass('active');
			currentStep = step;
			currentStep.addClass('active');
			currentID = id;
			//smoothly scroll to title
			if (!mailster.util.inViewport(currentStep.find('h2').get(0)))
				window.scrollTo({
					top: 0,
					left: 0,
					behavior: 'smooth',
				});
		}
	}

	var busy = false;
	function query_templates(reload_license) {
		busy = true;
		var templates = $('.templates');

		templates.addClass('loading');

		mailster.util.ajax(
			'query_templates',
			{
				search: null,
				type: null,
				browse: 'samples',
				page: 1,
				reload_license: reload_license,
			},
			function (response) {
				busy = false;
				templates.removeClass('loading');
				if (response.success) {
					$('.templates').html(response.data.html);
				}
			},
			function (jqXHR, textStatus, errorThrown) {
				busy = false;
				templates.removeClass('loading');
			}
		);
	}

	function quickInstall(id, slug, action, context, cb) {
		status.html(mailster.l10n.setup.install_addon);
		spinner.css('visibility', 'visible');
		var el = $('#deliverytab-' + id);

		mailster.util.ajax(
			'quick_install',
			{
				plugin: slug,
				step: action,
				context: context,
			},
			function (response) {
				if (response.success) {
					if (response.data.next) {
						quickInstall(
							id,
							slug,
							response.data.next,
							['deliverymethod_tab_' + id],
							cb
						);
					} else if (response.data.content) {
						el.html(response.data.content);
						status.html('');
						spinner.css('visibility', 'hidden');
						cb && cb(response);
					}
				} else {
				}
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	return mailster;
})(mailster || {}, jQuery, window, document);
