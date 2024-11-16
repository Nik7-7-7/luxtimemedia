export let toCamelCase = (s) => s.replace(/-./g, (x) => x[1].toUpperCase())

export let toKebapCase = (s) => s.replace(/([a-z])([A-Z])/g, '$1-$2')
	.replace(/\s+/g, '-')
	.toLowerCase();

export function kebabToTitleCase(kebabCaseString) {
	const words = kebabCaseString.split('-');
	const capitalizedWords = words.map(word => word.charAt(0).toUpperCase() + word.slice(1));
	const titleCaseString = capitalizedWords.join(' ');

	return titleCaseString;
}

export let waitForEl = (selector) => {
	return new Promise((resolve) => {
		if (document.querySelector(selector)) {
			return resolve(document.querySelector(selector));
		}

		const observer = new MutationObserver((mutations) => {
			if (document.querySelector(selector)) {
				resolve(document.querySelector(selector));
				observer.disconnect();
			}
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true,
		});
	});
};

// You can choose to have an element with the class "window-top" inside of your draggable window that will act as the "handle" for the window or it will attach to the element itself

export function makeDraggable(elmnt, handleSelector) {
	// Make an element draggable (or if it has a .window-top class, drag based on the .window-top element)
	let currentDiffX = 0,
		currentDiffY = 0,
		xReference = 0,
		yReference = 0,
		translate,
		needForRAF = true,
		currentX,
		currentY;

	// If there is a window-top classed element, attach to that element instead of full window
	if (elmnt.querySelector(handleSelector)) {
		// If present, the window-top element is where you move the parent element from
		elmnt
			.querySelector(handleSelector)
			.addEventListener("mousedown", dragMouseDown);
	} else {
		// Otherwise, move the element itself
		elmnt.addEventListener("mousedown", dragMouseDown);
	}

	function dragMouseDown(e) {
		// Prevent any default action on this element (you can remove if you need this element to perform its default action)
		e.preventDefault();

		// Set reference for movement to current mouseposition
		xReference = e.clientX;
		yReference = e.clientY;
		// When the mouse is let go, call the closing event
		document.addEventListener("mouseup", closeDragElement);
		// call a function whenever the cursor moves
		document.addEventListener("pointermove", elementDrag);
		// Get current state of the transform of the window
		translate = getTranslateValues(elmnt.style.transform);
		//prevent iframe from capturing mousemove event during drag
		removePointerEventsFromAllIframes();
	}

	function elementDrag(e) {
		// Prevent any default action on this element (you can remove if you need this element to perform its default action)
		e.preventDefault();

		if (needForRAF) {
			needForRAF = false;
			currentX = e.clientX;
			currentY = e.clientY;
			requestAnimationFrame(updateDragPosition);
		}
	}

	function updateDragPosition() {
		needForRAF = true;

		//check how much the mouse moved since the mouse was held down
		currentDiffY = yReference - currentY;
		currentDiffX = xReference - currentX;
		// Set the element's new position
		elmnt.style.transform = `translate( ${translate.x - currentDiffX}px, ${translate.y - currentDiffY
			}px)`;
	}

	function closeDragElement() {
		// Stop moving when mouse button is released and release events
		document.removeEventListener("mouseup", closeDragElement);
		document.removeEventListener("pointermove", elementDrag);
		addPointerEventsBackToAllIframes();
	}
}

export function removePointerEventsFromAllIframes() {
	document.querySelectorAll("iframe").forEach((el) => {
		document.querySelector("iframe").style.pointerEvents = "none";
	});
}

export function addPointerEventsBackToAllIframes() {
	document.querySelectorAll("iframe").forEach((el) => {
		document.querySelector("iframe").style.pointerEvents = null;
	});
}

function getTranslateValues(transformValue) {
	const regex = /translate\(\s*([\d.-]+)px,\s*([\d.-]+)px\)/;
	const match = transformValue.match(regex);
	if (match && match.length >= 3) {
		const xValue = parseInt(match[1]);
		const yValue = parseInt(match[2]);

		return { x: xValue, y: yValue };
	} else {
		return { x: null, y: null };
	}
}

export const getTypeOfCssProperty = (function () {
	// Initialize the Map with the CSS properties as keys and their categories as values
	const cssMap = new Map();

	// Define the CSS properties for each category

	const colorProperties = [
		"background-color",
		"color",
		"border-color",
		"background-color-hover",
		"color-hover",
		"border-color-hover",
		"outline-color",
		"caret-color",
		"column-rule-color",
		"fill",
		"stroke",
		"stop-color",
		"text-decoration-color",
		"text-emphasis-color",
		"text-shadow",
		"box-shadow",
		"outline",
		"border",
		"border-top",
		"border-right",
		"border-bottom",
		"border-left",
		"column-rule",
		"background",
		"list-style",
		"caret-color",
		"text-decoration",
		"text-emphasis",
		"link-color",
		"link-color-hover",
		"border-top-color",
		"border-left-color",
		"border-bottom-color",
		"border-right-color",
		"border-top-color-hover",
		"border-left-color-hover",
		"border-bottom-color-hover",
		"border-right-color-hover",
	];

	const typographyProperties = [
		"font-family",
		"font-size",
		"font-weight",
		"font-style",
		"font-variant",
		"line-height",
		"letter-spacing",
		"text-align",
		"text-decoration",
		"text-transform",
		"text-indent",
		"text-overflow",
		"text-shadow",
		"text-wrap",
		"white-space",
		"word-spacing",
		"word-break",
		"word-wrap",
		"overflow-wrap",
		"hyphens",
	];

	const widthProperties = ["width", "min-width", "max-width", "column-width"];

	const spacingProperties = [
		// Margin
		"margin",
		"margin-top",
		"margin-right",
		"margin-bottom",
		"margin-left",

		// Padding
		"padding",
		"padding-top",
		"padding-right",
		"padding-bottom",
		"padding-left",

		// Gap (grid and flexbox)
		"gap",
		"row-gap",
		"column-gap",

		// Spacing for flexbox
		"flex",
		"flex-basis",

		// Spacing for CSS Grid Layout
		"grid-row-gap",
		"grid-column-gap",
		"grid-gap",
	];

	const radiusProperties = [
		// Border radius
		"border-radius",
		"border-top-left-radius",
		"border-top-right-radius",
		"border-bottom-left-radius",
		"border-bottom-right-radius",
	];

	const heightProperties = ["height", "min-height", "max-height"];

	// Populate the Map with the CSS properties and their respective categories
	typographyProperties.forEach((property) =>
		cssMap.set(property, "typography")
	);
	radiusProperties.forEach((property) => cssMap.set(property, "radius"));
	spacingProperties.forEach((property) => cssMap.set(property, "spacing"));
	widthProperties.forEach((property) => cssMap.set(property, "width"));
	colorProperties.forEach((property) => cssMap.set(property, "colors"));
	heightProperties.forEach((property) => cssMap.set(property, "height"));

	// Return the actual function that performs the mapping
	return function (cssProperty) {
		return cssMap.get(cssProperty);
	};
})();
