document.documentElement.setAttribute('hidden', true);

(function () {
	'use strict';
	var oldUrl,
		lastanimation = '',
		form = document.querySelector(
			'.wp-block-mailster-form-outside-wrapper-placeholder'
		);

	document.documentElement.removeAttribute('hidden');

	if (form && form.classList.contains('is-empty')) {
		form.scrollIntoView({
			behavior: 'auto',
			block: 'center',
			inline: 'nearest',
		});
	}

	document.querySelectorAll('a[href]').forEach(function (el) {
		el.style.cursor = 'not-allowed';
		el.addEventListener('click', function (event) {
			event.preventDefault();
			return false;
		});
	});

	window.addEventListener('message', function (event) {
		var data;

		try {
			data = JSON.parse(event.data);
			if (!data.id) return;
		} catch (e) {
			return;
		}

		var params = new URLSearchParams();

		params.set('context', 'edit');
		params.set('_locale', 'user');
		params.set('attributes[id]', data.id);
		data.options.align && params.set('attributes[align]', data.options.align);

		var args = {
			width: data.options.width,
			padding: data.options.padding,
			classes: ['mailster-block-form-type-' + data.type],
			isPreview: true,
		};

		if (data.type != 'content') {
			args['triggers'] = data.options.triggers;
			args['trigger_delay'] = 2;
			args['trigger_inactive'] = 4;
			args['trigger_scroll'] = data.options.trigger_scroll;
		}

		var url = 'wp/v2/block-renderer/mailster/form?' + params.toString();

		if (url != oldUrl) {
			wp.apiFetch({
				method: 'POST',
				path: url,
				data: { block_form_content: data.post_content, args: args },
			})
				.then(function (post) {
					document.querySelector(
						'.wp-block-mailster-form-outside-wrapper-' + data.id
					).outerHTML = post.rendered;

					updateForm();

					return post;
				})
				.catch(function (err) {
					var el = document.querySelector(
						'.wp-block-mailster-form-outside-wrapper-' + data.id
					);
					el.classList.add('has-error');
					el.innerHTML = err.message;

					event.source.postMessage(
						JSON.stringify({
							success: false,
							error: err,
							location: location.origin + location.pathname,
						}),
						event.origin
					);
				})
				.finally(function () {});
		} else {
			updateForm();
		}

		function getCSS() {
			var css = {};

			css['flex-basis'] = data.options.width
				? Math.min(96, data.options.width) + '%'
				: '100%';
			if (data.options.padding) {
				css['paddingTop'] = data.options.padding.top || 0;
				css['paddingRight'] = data.options.padding.right || 0;
				css['paddingBottom'] = data.options.padding.bottom || 0;
				css['paddingLeft'] = data.options.padding.left || 0;
			}

			return css;
		}

		function reloadFormScript() {
			var script = document.getElementById('mailster-form-view-script-js');

			var clone = document.createElement('script');
			clone.id = 'mailster-form-view-script-js';
			clone.src = script.src;

			script.parentNode.removeChild(script);
			document.head.appendChild(clone);
		}

		function updateForm() {
			var form = document.querySelector(
				'.wp-block-mailster-form-outside-wrapper-' + data.id
			);
			form.classList.remove('has-animation', 'animation-' + lastanimation);

			if (data.options.animation) {
				form.classList.add(
					'has-animation',
					'animation-' + data.options.animation
				);
				lastanimation = data.options.animation;
			}

			var mailsterForm = form.querySelector('.mailster-block-form');
			Object.assign(mailsterForm.style, getCSS());

			oldUrl = url;
			event.source.postMessage(
				JSON.stringify({
					success: true,
					location: location.origin + location.pathname,
				}),
				event.origin
			);

			reloadFormScript();
		}
	});

	function getScrollPercent() {
		var el = document.documentElement,
			body = document.body,
			st = 'scrollTop',
			sh = 'scrollHeight';
		var x = (el[st] || body[st]) / ((el[sh] || body[sh]) - el.clientHeight);
		return parseFloat((x * 100).toFixed());
	}

	function getScrollPosition() {
		return document.documentElement['scrollTop'] || document.body['scrollTop'];
	}

	function info() {
		var infoScreen = document.createElement('div');
		Object.assign(infoScreen.style, {
			position: 'fixed',
			top: 0,
			right: 0,
		});
		infoScreen.innerHTML = '0';
		document.body.appendChild(infoScreen);

		var form = document.querySelector(
			'.wp-block-mailster-form-outside-wrapper-placeholder'
		);

		var siblings = Array.from(form.parentNode.children).filter(function (
			element
		) {
			return (
				element !== form &&
				(element.tagName === 'H2' || element.tagName === 'P')
			);
		});
		siblings.forEach(function (sibling) {
			sibling.style.outline = '1px dotted red';
		});

		['scroll', 'touchstart'].forEach(function (name) {
			window.addEventListener(
				name,
				debounce(function () {
					infoScreen.innerHTML = getScrollPercent();
				}, 5),
				true
			);
		});
	}

	function debounce(func, wait, immediate) {
		var timeout;

		return function executedFunction() {
			var context = this;
			var args = arguments;

			var later = function () {
				timeout = null;
				if (!immediate) {
					func.apply(context, args);
				}
			};

			var callNow = immediate && !timeout;

			clearTimeout(timeout);

			timeout = setTimeout(later, wait);

			if (callNow) {
				func.apply(context, args);
			}
		};
	}
})();
