// block DOM
mailster = (function (mailster, $, window, document) {
	'use strict';

	mailster.$.document.on('submit', '#post', function () {
		$('<input />')
			.attr('type', 'hidden_')
			.attr('name', 'from_workflow')
			.attr('value', '1')
			.appendTo('#post');
	});

	var params = new URL(window.location).searchParams;
	if (params.has('close_modal') && window.parent) {
		window.parent.mailster_receiver_post_id(params.get('post'));
	}

	return mailster;
})(mailster || {}, jQuery, window, document);
// end DOM
