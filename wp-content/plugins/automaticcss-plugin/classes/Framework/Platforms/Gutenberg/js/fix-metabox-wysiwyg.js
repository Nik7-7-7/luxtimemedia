(function () {
	window.addEventListener("load", () => {
		if (!automatic_css_block_editor_options) {
			console.error(
				"automatic_css_block_editor_options is not defined. Please check if the plugin is activated."
			);
			return;
		}
		let root_font_size =
			automatic_css_block_editor_options.root_font_size + "%";
		// Metabox WYSIWYG editors are inheriting the background and text color from the editor.
		// This might not be intended, especially with dark backgrounds.
		const metabox_iframes = document.querySelectorAll(
			".rwmb-input .wp-editor-container iframe"
		);
		metabox_iframes.forEach((iframe) => {
			// console.log(
			// 	"Metabox iframe detected, fixing rfs and resetting its background and text color",
			// 	iframe
			// );
			const iframe_html =
				iframe.contentDocument.querySelector("html") ||
				iframe.contentWindow.document.querySelector("html");
			iframe_html.style.fontSize = root_font_size;
			const iframe_body = iframe.contentDocument.querySelector("#tinymce");
			iframe_body.style.backgroundColor = "initial";
			iframe_body.style.color = "initial";
			const iframe_headings = iframe.contentDocument.querySelectorAll("h1, h2, h3, h4, h5, h6");
			iframe_headings.forEach((heading) => {
				heading.style.color = "initial";
			});
		});
	});
})();
