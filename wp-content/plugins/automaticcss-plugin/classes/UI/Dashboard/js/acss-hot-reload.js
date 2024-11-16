(function () {
	window.onstorage = function (evt) {
		if (evt.key !== 'ACSS_Refresh') {
			return;
		}
		console.log('ACSS hot reloading has been triggered');
		let refreshToken = JSON.parse(window.localStorage.getItem('ACSS_Refresh'));
		if (refreshToken) {
			console.log('ACSS stylesheets refresh has been triggered');
			let stylesheets = document.querySelectorAll(
				"link[rel=stylesheet][id^=automaticcss-]",
			);
			if (stylesheets.length !== 0) {
				stylesheets.forEach((stylesheet) => {
					let id = stylesheet.getAttribute("id");
					console.log("Reloading stylesheet: " + id);
					let href = stylesheet.getAttribute("href");
					// Check if there's already a query parameter
					if (href.includes("?")) {
						// Remove existing query parameter
						href = href.split("?")[0];
					}
					// Add a random number as a query parameter to bust cache
					let randomParam = "acss_rand=" + refreshToken.hashedSettings;
					if (href.includes("?")) {
						// If there's already a query parameter, append with '&'
						href += "&" + randomParam;
					} else {
						// Otherwise, append with '?'
						href += "?" + randomParam;
					}
					stylesheet.setAttribute("href", href);
				});
			}
			// Reload the inline CSS with ID 'automaticcss-core-inline-css'
			let inlineStyle = document.getElementById('automaticcss-core-inline-css');
			if (inlineStyle) {
				console.log("Reloading inline style: automaticcss-core-inline-css");
				inlineStyle.innerHTML = refreshToken.globalCSS;
			}
		}
	};
})();
