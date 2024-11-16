import { getTypeOfCssProperty, toCamelCase, toKebapCase } from "../helpers";
import { BuilderApi } from "./types";

export let gutenbergFunctions: BuilderApi = {
	name: "gutenberg",
	getIframeDocument: () => {
		//iframe is only present in tablet or mobile mode
		if (document.querySelector("iframe[name='editor-canvas']"))
			return document.querySelector("iframe[name='editor-canvas']")
				.contentWindow.document;
		//in normal mode return the content area
		return (
			document.querySelector(".edit-post-visual-editor__content-area") ??
			document.querySelector(".editor-styles-wrapper")
		);
	},

	mainInputSelector: ".acss-class-input input",
	mainPanelSelector:
		".interface-navigable-region.interface-interface-skeleton__sidebar",
	classInputSelector: ".acss-class-input input",

	getCurrentElementId: function () {
		return (
			"#block-" +
			wp.data.select("core/block-editor").getSelectedBlock().clientId
		);
	},

	addClass: function (classString) {
		this.removePreviewClass(classString);
		// Get the selected block
		const selectedBlock = wp.data
			.select("core/block-editor")
			.getSelectedBlock();

		// Add a class to the block's attributes
		const updatedAttributes = {
			...selectedBlock.attributes,
			className: `${
				selectedBlock.attributes.className ?? ""
			} ${classString}`.trim(),
		};

		// Update the block's attributes with the new class
		wp.data
			.dispatch("core/block-editor")
			.updateBlockAttributes(selectedBlock.clientId, updatedAttributes);
	},

	removeClass: function (classString) {
		// Get the selected block
		const selectedBlock = wp.data
			.select("core/block-editor")
			.getSelectedBlock();

		const oldClassString = selectedBlock.attributes.className;

		// Regular expression pattern to match the class with optional surrounding spaces
		var pattern = new RegExp("\\b\\s*" + classString + "\\s*\\b", "g");

		// Remove the class from the string and trim extra spaces
		var modifiedClasses = oldClassString.replace(pattern, "").trim();

		// Add a class to the block's attributes
		const updatedAttributes = {
			...selectedBlock.attributes,
			className: `${modifiedClasses}`,
		};

		// Update the block's attributes with the new class
		wp.data
			.dispatch("core/block-editor")
			.updateBlockAttributes(selectedBlock.clientId, updatedAttributes);
	},
	isClassAlreadyActive: function (classString) {
		// Get the selected block
		const activeClasses =
			wp.data
				.select("core/block-editor")
				.getSelectedBlock()
				.attributes.className?.split(" ") ?? [];
		return activeClasses.includes(classString);
	},
	getCurrentInputOption: () => {
		return false;
	},
	setUnitToNone: () => {
		//not needed for gb right now
		return;
	},
	initHoverPreviewEventImplementation: (initHoverPreview) => {
		//Not needed?
	},

	getCurrentInput: function (eventTarget) {
		return eventTarget;
	},

	getCurrentSelector: function () {
		return this.getCurrentElementId();
		return false;
	},

	getCurrentCssProperty: function (currentInput) {
		//color swatches in generate need special treatment
		if (
			currentInput.classList.contains(
				"gblocks-color-component__toggle-indicator"
			)
		) {
			const colorComponent = currentInput.closest(".gblocks-color-component");

			//first, border colors
			if (currentInput.closest(".gblocks-border-colors")) {
				//find out what color needs to change
				const borderInputGroup = currentInput.closest(".gblocks-flex-control");
				const inputId = borderInputGroup.querySelector(
					".components-text-control__input"
				).id;
				const match = inputId.match(/border([a-zA-Z]+)-/);
				const borderSide = match[1].toLowerCase();
				if (
					colorComponent.previousElementSibling?.classList.contains(
						"gblocks-color-component"
					)
				)
					return `border-${borderSide}-color-hover`;
				return `border-${borderSide}-color`;
			}

			const colorRow = currentInput.closest(".gblocks-color-group__row");
			const colorType = colorRow?.querySelector(
				".gblocks-color-group__row-label"
			)?.textContent;
			if (!colorType || !colorRow) return false;
			//there are two color components (swatches) side by side, the first one is the normal color, the second one always the hover

			if (
				colorComponent.previousElementSibling?.classList.contains(
					"gblocks-color-component"
				)
			) {
				switch (colorType) {
					case "Background":
						return "background-color-hover";
					case "Text":
						return "color-hover";
					case "Link":
						return "link-color-hover";
				}
			}

			switch (colorType) {
				case "Background":
					return "background-color";
				case "Text":
					return "color";
				case "Link":
					return "link-color";
			}
		} else if (currentInput.id.slice(0, 8) === "gblocks-")
			//transform for all the weird mixed naming conventions generate has on their inputs
			return toKebapCase(toCamelCase(currentInput.id.slice(8)));
		else return toKebapCase(toCamelCase(currentInput.id));

		return false;
	},

	getExcludedInputSelectors: function () {
		return "#thisisjustaplaceholder";
	},

	getVarSections: function (currentInput) {
		const propertyType = getTypeOfCssProperty(
			this.getCurrentCssProperty(currentInput)
		);
		switch (propertyType) {
			case "typography":
				return ["typography"];
			case "spacing":
				return ["spacing", "section", "button", "frames-card"];
			case "radius":
				return ["radius", "button", "frames-card"];
			case "width":
				return ["width"];
			case "grid":
				return ["grid"];
			case "colors":
				return ["colors"];
			case "height":
				return ["height"];
			case "grid-column":
				return ["grid-column"];
			default:
				return [""];
		}
	},

	//This maps every cssproperty to the corresponing category in generate which is needed to save the value correctly, it is memoized for better performance
	getCategoryFromCssProperty: (function () {
		// Initialize the Map with the CSS properties as keys and their categories as values
		const cssMap = new Map();

		// Define the CSS properties for each category
		const typographyProperties = [
			"font-size",
			"font-family",
			"font-weight",
			"line-height",
			"letter-spacing",
		];
		const borderProperties = [
			"border-top-left-radius",
			"border-bottom-left-radius",
			"border-top-right-radius",
			"border-bottom-right-radius",
			"border-top-width",
			"border-left-width",
			"border-bottom-width",
			"border-right-width",
			"border-top-color",
			"border-left-color",
			"border-bottom-color",
			"border-right-color",
			"border-top-color-hover",
			"border-left-color-hover",
			"border-bottom-color-hover",
			"border-right-color-hover",
		];
		const spacingProperties = [
			"margin-top",
			"margin-left",
			"margin-right",
			"margin-bottom",
			"padding-top",
			"padding-left",
			"padding-right",
			"padding-bottom",
		];
		const sizingProperties = [
			"width",
			"height",
			"min-width",
			"min-height",
			"max-height",
			"max-width",
		];
		const colorProperties = [
			"color",
			"background-color",
			"link-color",
			"color-hover",
			"background-color-hover",
			"link-color-hover",
		];

		// Populate the Map with the CSS properties and their respective categories
		typographyProperties.forEach((property) =>
			cssMap.set(property, "typography")
		);
		borderProperties.forEach((property) => cssMap.set(property, "borders"));
		spacingProperties.forEach((property) => cssMap.set(property, "spacing"));
		sizingProperties.forEach((property) => cssMap.set(property, "sizing"));
		colorProperties.forEach((property) => cssMap.set(property, "colors"));

		// Return the actual function that performs the mapping
		return function (cssProperty) {
			return cssMap.get(cssProperty);
		};
	})(),

	setCssProperty: function (cssProperty, cssValue) {
		const selectedBlock = wp.data
			.select("core/block-editor")
			.getSelectedBlock();
		const category = this.getCategoryFromCssProperty(cssProperty);

		let updatedAttributes = {
			...selectedBlock.attributes,
		};

		//check if we are in tablet or mobile mode and append the correct string
		const rensponsiveTabs = document.querySelector(".gb-responsive-tabs");
		let responsiveString = "";
		if (rensponsiveTabs?.querySelector("button.is-pressed:nth-child(2)"))
			responsiveString = "Tablet";
		else if (rensponsiveTabs?.querySelector("button.is-pressed:nth-child(3)"))
			responsiveString = "Mobile";
		cssProperty = cssProperty + responsiveString;
		//colors are not in a category and are stored in the root of the block attributes
		if (category === "colors") {
			if (cssProperty === "color") cssProperty = "textColor"; //color is stored as textColor
			updatedAttributes = {
				...selectedBlock.attributes,
				[toCamelCase(cssProperty)]: cssValue,
			};
		} else {
			updatedAttributes[category] = {
				...selectedBlock.attributes[category],
				[toCamelCase(cssProperty)]: cssValue,
			};
		}

		// Update the block's attributes with the new class
		wp.data
			.dispatch("core/block-editor")
			.updateBlockAttributes(selectedBlock.clientId, updatedAttributes);
	},

	displayValuePreview: function (value, currentInput, previewStyleElement) {
		let currentSelector = this.getCurrentSelector();

		let currentCssProperty = this.getCurrentCssProperty(currentInput);

		if (currentCssProperty) {
			if (currentCssProperty.slice(-5) === "hover")
				currentCssProperty = currentCssProperty.slice(
					0,
					currentCssProperty.length - 6
				);
			previewStyleElement.innerHTML = `${currentSelector} {${currentCssProperty}: ${value}}`;
		}
	},

	removeValuePreview: function (currentInput, previewStyleElement) {
		previewStyleElement.innerHTML = "";
	},

	setValue: function (value, currentInput) {
		let currentCssProperty = this.getCurrentCssProperty(currentInput);
		this.setCssProperty(currentCssProperty, value);
	},
};
