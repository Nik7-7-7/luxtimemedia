import { oxygenFunctions } from "./builder-apis/oxygen.ts";
import { bricksFunctions } from "./builder-apis/bricks.ts";
import { gutenbergFunctions } from "./builder-apis/gutenberg.ts";
import { breakdanceFunctions } from "./builder-apis/breakdance.ts";

let sharedFunctions = {
	previewClass: function (classString: string) {
		let elementId = this.getCurrentElementId();

		this.getIframeDocument()
			.querySelector(elementId)
			.classList.add(classString);
	},
	removePreviewClass: function (classString: string) {
		let elementId = this.getCurrentElementId();
		if (this.isClassAlreadyActive(classString)) return; //if the class is already active in the element, don't remove it
		this.getIframeDocument()
			.querySelector(elementId)
			.classList.remove(classString);
	},
	getActiveClassesAsArray: function () {
		let classString = this.getIframeDocument().querySelector(
			this.getCurrentElementId()
		).classList.value;
		return classString.split(" ");
	},

	sanitizeElementLabel(label: string) {
		if (!label) return;

		let sanitizedLabel = label;
		//remove anything between brackets
		sanitizedLabel = sanitizedLabel.replace(/\([^\)]*\)/g, ""); //round brackets
		sanitizedLabel = sanitizedLabel.replace(/\[[^\]]*\]/g, ""); //square brackets
		//remove all special characters but keep spaces
		sanitizedLabel = sanitizedLabel.replace(/[^a-zA-z0-9-_ ]/g, "");
		//trim multiple spaces down to one space
		sanitizedLabel = sanitizedLabel.replace(/\s+/g, " ");
		//remove leading and trailing spaces
		return sanitizedLabel.trim();
	},

	getAcssStylesheets() {
		return [...this.getIframeDocument().styleSheets].filter((styleSheet) =>
			["automaticcss-core-css", "automaticcss-bricks-css"].includes(
				styleSheet.ownerNode.id
			)
		);
	},

	getCssClassDefinition(classString: string) {
		let acssStyleSheets = this.getAcssStylesheets();

		for (var i = 0; i < acssStyleSheets.length; i++) {
			var styleSheet = acssStyleSheets[i];
			// Handle different types of style sheets
			var rules = styleSheet.rules || styleSheet.cssRules;
			// Loop through all the rules in the stylesheet
			for (var j = 0; j < rules.length; j++) {
				var rule = rules[j];
				// Check if the rule is a CSSStyleRule and has the target class
				if (rule.selectorText === "." + classString) {
					return rule.cssText;
				}
			}
		}
	},

	getCssVariableDefinition(variableString: string) {
		let acssStyleSheets = this.getAcssStylesheets();

		// remove trailing '-' adn leading ' ' if present
		let varName = variableString.replace(/^-+|\s+$/g, "");
		// make sure var name alwasy starts with '--'
		varName = "--" + varName;

		for (var i = 0; i < acssStyleSheets.length; i++) {
			var styleSheet = acssStyleSheets[i];
			// Handle different types of style sheets
			var rules = styleSheet.rules || styleSheet.cssRules;
			// Loop through all the rules in the stylesheet
			for (var j = 0; j < rules.length; j++) {
				var rule = rules[j];
				// As vars are configured at root level get all :root definitions
				if (
					rule.selectorText?.startsWith(":root") &&
					[...rule.style].includes(varName)
				) {
					const match = rule.style.cssText.match(
						new RegExp(`${varName}:\\s(.*?;)`)
					);
					if (match && match[1]) {
						return match[1];
					}
				}
			}
		}
	},
};

let builderFunctions = {};

if (document.getElementById("ct-artificial-viewport")) {
	builderFunctions = oxygenFunctions;
} else if (document.querySelector(".brx-body")) {
	builderFunctions = bricksFunctions;
} else if (document.querySelector("div.v-application")) {
	builderFunctions = breakdanceFunctions;
} else {
	builderFunctions = gutenbergFunctions;
}

//@ts-ignore
window.builderTest = { ...sharedFunctions, ...builderFunctions };

export let Builder = { ...sharedFunctions, ...builderFunctions };
