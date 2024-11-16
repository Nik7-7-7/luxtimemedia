import { toKebapCase } from "../helpers";
import { BuilderApi } from "./types";

declare let $scope: any;

export let oxygenFunctions: BuilderApi = {
	/**SELECTORS AND MISC */
	name: "oxygen",
	getIframeDocument: () =>
		//@ts-ignore
		document.getElementById("ct-artificial-viewport").contentWindow.document,
	mainInputSelector: ".oxygen-active-selector-box",
	mainPanelSelector: "#oxygen-sidebar",
	classInputSelector: ".oxygen-classes-dropdown-input",
	saveClassSelector: ".input-wrapper .actions .create",

	/** INPUT VALUE MANIPULATION */

	displayValuePreview: function (value, currentInput, previewStyleElement) {
		currentInput.value = value;
		currentInput.dispatchEvent(new Event("input"));
	},

	removeValuePreview: function (currentInput, previewStyleElement) {
		currentInput.value = "";
		currentInput.dispatchEvent(new Event("input"));
	},

	setValue: function (value, currentInput) {
		currentInput.value = value;
		currentInput.dispatchEvent(new Event("input"));
	},

	/**BUILDER MANIPULATION */

	setUnitToNone: function (currentInput) {
		let currentOption = this.getCurrentInputOption(currentInput);

		if (!currentInput.parentElement.querySelector(".oxygen-measure-box-units"))
			return; //if there is no unit selection box, no need to set any unit

		if (currentOption) {
			$scope.iframeScope.setOptionUnit(currentOption, " ");
		}
	},

	rerenderStyles: function () {
		return;
	},

	addStructurePanelButton: async function (options) {
		const structurePanel = document.querySelector("#ct-sidepanel");
		structurePanel.addEventListener("mouseover", (e) => {
			const structureItem = e.target.closest(".dom-tree-node");
			if (!structureItem) return;
			if (structureItem.querySelector(".bem-generator")) return;

			if (structureItem.querySelectorAll(".dom-tree-node").length === 0) return; // no need for bem button if there are no children

			const newButton = document.createElement("span");
			newButton.innerHTML = options.icon;
			newButton.style.height = "18px";
			newButton.style.width = "18px";
			newButton.classList.add("action", "bem-generator");
			newButton.addEventListener("click", (e) => {
				const elementId = e.target
					.closest(".dom-tree-node")
					.getAttribute("ng-attr-tree-id");
				options.onClick(e, elementId);
			});
			const actionsContainer = structureItem.querySelector(
				".dom-tree-node-options"
			);
			actionsContainer.insertBefore(newButton, actionsContainer.firstChild);
		});
	},

	changeLabelOfElement: function (label, elementId) {
		let element = this._getElementById(elementId);
		if (!element || !label) return;

		element.options.nicename = label;

		return true;
	},

	setActiveSelector(selector, elementId) {
		let element = this._getElementById(elementId);
		element.options.activeselector = selector;
		$scope.iframeScope.switchEditToId();
	},

	/** CLASS MANIPULATION */

	copyStylesFromClass(sourceClass, targetClass) {
		let classOptions;
		if ($scope.iframeScope.classes[sourceClass])
			classOptions = $scope.iframeScope.getClassOptionsToCopy(sourceClass);
		if (classOptions) {
			$scope.iframeScope.classes[targetClass] = classOptions;
			$scope.iframeScope.classesCached = false;
			$scope.iframeScope.outputCSSOptions();
			//Maybe we also need $scope.iframeScope.rebuildDOM() , but i think its not nececcary
		}
	},

	removeClass: function (classString) {
		$scope.iframeScope.removeComponentClass(classString);
	},

	addClass: function (classString) {
		this.removePreviewClass(classString);
		$scope.iframeScope.addSuggestedClassToComponent(classString);
	},

	renameClass: function (oldClassName, newClassName) {
		alert("Rename in Oxygen is not possible");
	},

	removeClassFromElement: function (className, elementId) {
		$scope.iframeScope.removeComponentClass(className, elementId);
	},

	deleteClassFromBuilder: function (className) {
		$scope.iframeScope.deleteClass(className);
	},

	addClassToElement: function (className, elementId) {
		$scope.iframeScope.addClassToComponentSafe(elementId, className);
		this.setActiveSelector(className, elementId);
	},

	isClassAlreadyActive: function (classString) {
		if (
			$scope.iframeScope.findComponentItem(
				$scope.iframeScope.componentsTree.children,
				$scope.iframeScope.component.active.id,
				$scope.iframeScope.getComponentItem
			).options.classes
		) {
			return $scope.iframeScope
				.findComponentItem(
					$scope.iframeScope.componentsTree.children,
					$scope.iframeScope.component.active.id,
					$scope.iframeScope.getComponentItem
				)
				.options.classes.includes(classString);
		}
	},

	/**INTERNAL */

	_getElementById: function (elementId) {
		return $scope.iframeScope.findComponentItem(
			$scope.iframeScope.componentsTree.children,
			elementId,
			$scope.iframeScope.getComponentItem
		);
	},

	_sanitizeElementLabel(label) {
		return label.replace(/[^a-zA-z0-9-_]/g, "");
	},

	/** GET STUFF DIRECTLY FROM THE BUILDER */

	getElementTree: function (elementId) {
		const element = this._getElementById(elementId);
		const elementTree = {};

		elementTree.options = {
			id: element.id,
			value: toKebapCase(this.sanitizeElementLabel(element.options.nicename)),
			label: element.options.nicename,
			cssClasses: element.options?.classes ?? [],
		};

		let getChildren = (oxyChildren) => {
			if (!oxyChildren) return;
			let children = [];
			oxyChildren.forEach((child) => {
				children.push({
					options: {
						id: child.id,
						value: toKebapCase(
							this.sanitizeElementLabel(child.options.nicename)
						),
						label: child.options.nicename,
						cssClasses: child.options?.classes ?? [],
					},
					children: getChildren(child.children) ?? [],
				});
			});
			return children;
		};

		elementTree.children = getChildren(
			$scope.iframeScope.getElementChildren(elementId)
		);

		return elementTree;
	},

	getElementLabel: function (elementId) {
		const element = this._getElementById(elementId);
		return element.options.nicename;
	},

	getCurrentElementId: function () {
		return (
			"#" +
			$scope.iframeScope.findComponentItem(
				$scope.iframeScope.componentsTree.children,
				$scope.iframeScope.component.active.id,
				$scope.iframeScope.getComponentItem
			).options.selector
		);
	},

	initHoverPreviewEventImplementation: (initHoverPreview) => {
		//Add Event listener to init the menu

		document
			.querySelector(".oxygen-classes-dropdown-input")
			.addEventListener("input", function () {
				let firstOtherClassListItem = document.querySelector(
					".oxygen-classes-suggestions > li:first-child"
				);

				if (firstOtherClassListItem == null) return;

				//if (firstOtherClassListItem.getAttribute("tabindex") === "0") return

				initHoverPreview();
			});
	},

	executeOnClassListOpen: function (callback) {
		//Add Event listener to init the menu

		document
			.querySelector(".oxygen-classes-dropdown-input")
			.addEventListener("input", function () {
				let firstOtherClassListItem = document.querySelector(
					".oxygen-classes-suggestions > li:first-child"
				);

				if (firstOtherClassListItem == null) return;

				//if (firstOtherClassListItem.getAttribute("tabindex") === "0") return

				callback();
			});
	},

	getUnusedClassListItems: function () {
		return document.querySelectorAll(".oxygen-classes-suggestions > li");
	},

	getClassStringFromListItem: function (classListItem) {
		return classListItem.lastElementChild.textContent;
	},

	getCurrentInputOption: (currentInput) => {
		let currentOption = currentInput.getAttribute("data-option");
		if (currentOption != null) {
			return currentOption;
		}
		if (
			currentInput.previousElementSibling &&
			currentInput.previousElementSibling.classList.contains(
				"oxygen-color-picker-color"
			)
		)
			return "color";
		return false;
	},

	getCurrentInput: function (eventTarget) {
		return eventTarget;
	},

	getCurrentSelector: function () {
		//not needed in oxygen
		return false;
	},

	getCurrentCssProperty: function (currentInput) {
		//not needed in oxygen
		return false;
	},

	getExcludedInputSelectors: function () {
		return ".oxygen-file-input";
	},
};
