mailster = (function (mailster, $, window, document) {
	'use strict';

	mailster.$.document.on('click', '.form_preview a', function () {
		var url = $(this).attr('href');
		var w = mailster.$.window.width() - 32;
		var h = mailster.$.window.height();
		var overlay = $('#form-preview-overlay');

		if (!overlay.length) {
			overlay = $(
				'<div id="form-preview-overlay"><iframe allowtransparency="true"></iframe></div>'
			).appendTo('body');
		}

		var iframe = overlay.find('iframe');
		iframe.attr('src', url);

		var title = $(this).data('title');

		//show thickbox with id form-preview-overlay
		mailster.util.tb_show(
			title,
			'#TB_inline?x=1' +
				'&width=' +
				w +
				'&height=' +
				h +
				'&inlineId=form-preview-overlay',
			null
		);
		return false;
	});

	return mailster;
})(mailster || {}, jQuery, window, document);
