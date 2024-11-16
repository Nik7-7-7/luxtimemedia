import { toKebapCase, waitForEl } from "../helpers";
import { BuilderApi } from "./types";

export let bricksFunctions: BuilderApi = {
	/**SELECTORS AND MISC */
	name: "bricks",
	mainInputSelector: "div.active-class",
	mainPanelSelector: "#bricks-panel-inner",
	classInputSelector:
		"#bricks-panel-element-classes > div.bricks-control-popup.bottom > div.input-wrapper > input[type=text]",
	saveClassSelector: ".input-wrapper .actions .create",

	findUnusedClass: function (classString) {
		let classListItems = this.getUnusedClassListItems();
		if (classListItems == null) return null;
		for (let classListItem of classListItems) {
			if (classListItem.firstElementChild?.textContent == classString) {
				return classListItem;
			}
		}
		return null;
	},

	sanitizeCssProperty: (cssProperty, currentInput) => {
		switch (cssProperty) {
			case "_width":
				return "width";
			case "_height":
				return "height";
			case "_widthMin":
				return "min-width";
			case "_heightMin":
				return "min-height";
			case "_widthMax":
				return "max-width";
			case "_heightMax":
				return "max-height";
			case "_top":
				return "top";
			case "_right":
				return "right";
			case "_bottom":
				return "bottom";
			case "_left":
				return "left";
			case "width-0":
				return "border-top-width";
			case "width-1":
				return "border-right-width";
			case "width-2":
				return "border-bottom-width";
			case "width-3":
				return "border-left-width";
			case "radius-0":
				return "border-top-left-radius";
			case "radius-1":
				return "border-top-right-radius";
			case "radius-2":
				return "border-bottom-right-radius";
			case "radius-3":
				return "border-bottom-left-radius";
			case "_typography":
				return "color";
			case "_background":
				return "background-color";
			case "_border":
				return "border-color";
			case "_columnGap":
				return "column-gap";
			case "_rowGap":
				return "row-gap";
			case "_gradient":
				return "";
			case "_border":
				return "border-color";
			case "_gridTemplateColumns":
				return "grid-template-columns";
			case "_gridGap":
				return "grid-gap";
			case "_gridTemplateRows":
				return "grid-template-rows";
			case "radius-top":
				return "border-top-left-radius";
			case "radius-right":
				return "border-top-right-radius";
			case "radius-left":
				return "border-bottom-left-radius";
			case "radius-bottom":
				return "border-bottom-right-radius";
			case "_gridItemColumnSpan":
				return "grid-column";
			default:
				if (cssProperty.charAt(0) == "_") return "";

				return cssProperty;
		}
	},

	getCurrentCssProperty: function (currentInput) {
		if (!currentInput) return "";
		let cssProperty = "";
		let handle = currentInput?.parentElement?.parentElement?.classList.contains(
			"handle"
		)
			? currentInput?.parentElement?.parentElement
			: null;
		if (handle && handle.title) {
			cssProperty = handle.title;
		} else if (
			currentInput.parentElement?.parentElement?.querySelector("label[for]") &&
			!currentInput.classList.contains("has-dynamic-picker") &&
			currentInput.parentElement?.parentElement
				?.querySelector("label[for]")
				?.getAttribute("for")
				?.slice(0, 3) !== "raw"
		) {
			cssProperty =
				currentInput.parentElement?.parentElement
					?.querySelector("label[for]")
					?.getAttribute("for") ?? "";
		} else if (currentInput.closest("[controlkey]")) {
			cssProperty =
				currentInput.closest("[controlkey]")?.getAttribute("controlkey") ?? "";
			if (cssProperty == "radius") {
				cssProperty =
					"radius-" + currentInput.closest(".handle")?.classList[1] ?? "";
			}
		} else if (currentInput.closest(".control-group")) {
			cssProperty =
				currentInput
					?.closest(".control-group")
					?.querySelector("[data-controlkey]")
					?.getAttribute("data-controlkey") ?? "";
		}
		return this.sanitizeCssProperty(cssProperty, currentInput);
	},

	initHoverPreviewEventImplementation: function (initHoverPreview) {
		//Add Event listener to init the menu

		document
			.querySelector("#bricks-panel-inner")
			?.addEventListener("mouseup", (e) => {
				if (e.target?.closest(this.mainInputSelector) === null) return;

				if (e.button != 0) return;

				waitForEl(this.classInputSelector).then(() => {
					initHoverPreview();

					document
						.querySelector(this.classInputSelector)
						.addEventListener("input", function () {
							let firstOtherClassListItem = document.querySelector(
								".css-classes ul:last-child li"
							);

							if (firstOtherClassListItem == null) return;

							if (firstOtherClassListItem.getAttribute("tabindex") === "0")
								return;

							initHoverPreview();
						});
				});
			});

		let enterHandler = function (e) {
			//if enter key is pressed
			if (e.key != "Enter") return;
			let initIfOpen = () => {
				if (document.querySelector(this.classInputSelector) != null) {
					initHoverPreview();

					document
						.querySelector(this.classInputSelector)
						.addEventListener("input", function () {
							let firstOtherClassListItem = document.querySelector(
								".css-classes ul:last-child li"
							);

							if (firstOtherClassListItem == null) return;

							if (firstOtherClassListItem.getAttribute("tabindex") === "0")
								return;

							initHoverPreview();
						});
				} else {
					setTimeout(initIfOpen, 100); //try again
				}
			};

			//only try if class input is closed (= is opened right now)
			if (document.querySelector(this.classInputSelector) == null) {
				setTimeout(initIfOpen, 100);
			}
		};

		document.addEventListener("keydown", enterHandler);
		this.getIframeDocument().addEventListener("keydown", enterHandler);
	},

	//exeutes the passed function when the classlist is open - can be used to add attributes to every classlist item

	executeOnClassListOpen: function (callback) {
		//new approach - just execute whenever the main input triggers an input event (MAKE SURE TO EXIT EARLY IN THE CALLBACK to avoid unnecessary computations)

		document
			.querySelector("#bricks-panel-inner")
			.addEventListener("input", (e) => {
				if (e.target.closest(this.classInputSelector) === null) return;
				callback();
			});
	},

	getExcludedInputSelectors: function () {
		return "#title, #url, #ariaLabel, #rel, #text, #bricks-panel-search, .external-url, #videoUrl, #cssSelector, #_cssClasses, #_cssId, div[data-control='conditions']";
	},

	/** INPUT VALUE MANIPULATION */

	removeValuePreview: function (currentInput, previewStyleElement) {
		previewStyleElement.innerHTML = "";
	},

	setValue: function (value, currentInput) {
		currentInput.value = value;

		currentInput.dispatchEvent(new Event("input"));
	},

	displayValuePreview: function (value, currentInput, previewStyleElement) {
		let currentCssProperty = this.getCurrentCssProperty(currentInput);
		const currentSelector = this.getCurrentSelector();
		if (currentCssProperty) {
			previewStyleElement.innerHTML = `${currentSelector} {${currentCssProperty}: ${value}}`;
		} else {
			currentInput.value = value;
			currentInput.dispatchEvent(new Event("input"));
		}
	},

	/**BUILDER MANIPULATION */

	setUnitToNone: function (currentInput) {
		//no need to do anything because Bricks automatically sets the unit to none for us when we input a var
	},

	addStructurePanelButton: async function (
		options = { icon: "BEM", onClick: () => {} }
	) {
		const structurePanel = document.querySelector("#bricks-structure");
		structurePanel.addEventListener("mouseover", (e) => {
			const structureItem = e.target.closest(".structure-item");
			if (!structureItem) return;
			if (structureItem.querySelector(".bem-generator")) return;

			if (
				structureItem.parentElement.querySelectorAll(".structure-item")
					.length === 1
			)
				return; // no need for bem button if there are no children

			const newButton = document.createElement("li");
			newButton.innerHTML = options.icon;
			newButton.querySelector("svg").setAttribute("style", "width: 18px");
			newButton.style.width = "24px";
			newButton.style.top = "3px";
			newButton.classList.add("action", "bem-generator");
			newButton.addEventListener("click", (e) => {
				const elementId =
					e.target.closest(".structure-item").parentElement.dataset.id;
				options.onClick(e, elementId);
			});
			let actionsContainer = structureItem.querySelector(".actions");
			if (!actionsContainer) {
				actionsContainer = document.createElement("ul");
				actionsContainer.classList.add("actions");
				structureItem.insertBefore(
					actionsContainer,
					structureItem.querySelector(".element-states")
				);
			}
			actionsContainer.insertBefore(newButton, actionsContainer.firstChild);
		});
	},

	changeLabelOfElement: function (label, elementId) {
		let element = this._getElementById(elementId);
		if (!element || !label) return;

		element.label = label;

		return true;
	},

	/**CLASS MANIPULATION */

	copyStylesFromClass(sourceClass, targetClass) {
		let bricksSourceClass = this._getBricksGlobalClassByName(sourceClass);
		let bricksTargetClass = this._getBricksGlobalClassByName(targetClass);
		if (!bricksSourceClass && !bricksTargetClass)
			alert("Copying styles failed: Source and target classes do not exist");
		if (!bricksTargetClass)
			alert("Copying styles failed: Target class does not exist");

		//if there are no settings, no need to copy anything
		if (bricksSourceClass?.settings)
			bricksTargetClass.settings = JSON.parse(
				JSON.stringify(bricksSourceClass.settings)
			);
		if (bricksSourceClass?.settings._cssCustom)
			bricksTargetClass.settings._cssCustom =
				this._getBricksInternalFunctions().$_replaceCustomCssRoot(
					bricksSourceClass.name,
					bricksTargetClass.name,
					bricksTargetClass.settings._cssCustom
				);
	},

	removeClass: function (className) {
		const activeElementId = this._getActiveElement().id;
		this.removeClassFromElement(className, activeElementId);
	},

	addClass: function (className) {
		this.removePreviewClass(className);
		const activeElementId = this._getActiveElement().id;
		this.addClassToElement(className, activeElementId);
	},

	renameClass: function (oldClassName, newClassName) {
		const globalClass = this._getBricksGlobalClassByName(oldClassName);
		if (!globalClass) return;
		if (globalClass?.settings?._cssCustom)
			globalClass.settings._cssCustom =
				this._getBricksInternalFunctions().$_replaceCustomCssRoot(
					oldClassName,
					newClassName,
					globalClass.settings._cssCustom
				);

		globalClass.name = newClassName;
	},

	rerenderStyles: function () {
		//HACK Trigger reload by resetting the global classes array
		this._getBricksState().globalClasses.push({});
		setTimeout(() => {
			this._getBricksState().globalClasses.pop();
		}, 100);
	},

	removeClassFromElement: function (className, elementId) {
		const element = this._getElementById(elementId);
		const cssClasses = element.settings._cssGlobalClasses;
		const classId = this._getBricksGlobalClassByName(className)?.id;
		if (!element || !cssClasses || cssClasses.indexOf(classId) === -1) return;

		cssClasses.splice(cssClasses.indexOf(classId), 1);
	},

	deleteClassFromBuilder: function (className) {
		const classId = this._getBricksGlobalClassByName(className)?.id;
		if (!classId) return;
		const bricksState = this._getBricksState();
		let globalClasses = bricksState.globalClasses;
		globalClasses.splice(
			globalClasses.findIndex((obj) => obj.id === classId),
			1
		);
	},

	addClassToElement: function (className, elementId) {
		let element = this._getElementById(elementId);
		if (!element) return;
		const bricksState = this._getBricksState();
		let elementClasses = element?.settings?._cssGlobalClasses;
		let classExists = false;
		function generateRandomId() {
			return (Math.random() + 1).toString(36).slice(-6);
		}
		let classId = generateRandomId();

		//check if class exist within Bricks Global Classes, or if it exists as an id
		bricksState.globalClasses.forEach((obj) => {
			if (obj.name === className) {
				classExists = true;
				classId = obj.id;
			} else if (obj.id === classId) {
				//in the unlikely event that the random id matches an existing id try a second time
				classId = generateRandomId();
			}
		});

		//add it if it does not exists yet
		if (!classExists) {
			let newGlobalClass = {
				id: classId,
				name: className,
				settings: {},
			};

			bricksState.globalClasses.push(newGlobalClass);
		}

		//if there is no class array create one
		if (!elementClasses) {
			if (!element.settings) element.settings = {};
			element.settings._cssGlobalClasses = [];
			elementClasses = element.settings._cssGlobalClasses;
		}

		//add the class if it not already exists on that element
		if (!elementClasses?.includes(classId)) {
			elementClasses.push(classId);
		}

		return true;
	},

	isClassAlreadyActive: function (className) {
		const classId = this._getBricksGlobalClassByName(className);
		if (!classId) return false;
		return this._getActiveElement().settings._cssGlobalClasses?.includes(
			classId
		);
	},

	/**FUNCTIONS FOR INTERNAL USE - DO NOT USE IN COMPONENTS */

	_getBricksGlobalClassById: function (classId) {
		const bricksState = this._getBricksState();
		return bricksState.globalClasses[bricksState.globalClassIndexById[classId]];
	},

	_getBricksGlobalClassByName: function (className) {
		const globalClasses = this._getBricksState().globalClasses;
		return globalClasses.find((el) => el.name === className);
	},

	_getBricksInternalFunctions: function () {
		return document.querySelector("[data-v-app]").__vue_app__.config
			.globalProperties;
	},

	_getBricksState() {
		//@ts-ignore
		return document.querySelector("[data-v-app]").__vue_app__.config
			.globalProperties.$_state;
	},

	_getBricksElements: function () {
		//@ts-ignore
		const bricksState = this._getBricksState();
		const allElements = bricksState.content
			.concat(bricksState.header)
			.concat(bricksState.footer);
		return allElements;
	},

	_getElementById: function (elementId) {
		const bricksElements = this._getBricksElements();
		return bricksElements.find((el) => el.id === elementId);
	},

	_getActiveElement: function () {
		return this._getBricksState().activeElement;
	},

	_getBricksClassNamesFromIdArray: function (idArray) {
		if (!idArray) return idArray;
		let classNameArray = [];
		idArray.forEach((id) => {
			let className = this._getBricksGlobalClassById(id)?.name;
			if (className) classNameArray.push(className);
		});
		return classNameArray;
	},

	/** GET STUFF DIRECTLY FROM THE BUILDER */

	getIframeDocument: function () {
		return document.getElementById("bricks-builder-iframe").contentWindow
			.document;
	},

	getElementIdFromStructurePanelButton: function (eventTarget) {},

	getActiveElementInternalId: function () {
		return this._getActiveElement().id;
	},

	getElementTree: function (elementId) {
		const element = this._getElementById(elementId);
		const elementTree = {};

		elementTree.options = {
			id: element.id,
			value: toKebapCase(
				this.sanitizeElementLabel(element.label) ||
					this.sanitizeElementLabel(element.name)
			),
			label:
				element.label ||
				element.name.charAt(0).toUpperCase() + element.name.slice(1),
			cssClasses: this._getBricksClassNamesFromIdArray(
				element.settings._cssGlobalClasses
			),
		};

		let getChildren = (childIds) => {
			if (!childIds) return;
			const children = [];
			childIds.forEach((childId) => {
				const child = this._getElementById(childId);
				children.push({
					options: {
						id: childId,
						value: toKebapCase(
							this.sanitizeElementLabel(child.label) ||
								this.sanitizeElementLabel(child.name)
						),
						label:
							child.label ??
							child.name.charAt(0).toUpperCase() + child.name.slice(1),
						cssClasses: this._getBricksClassNamesFromIdArray(
							child.settings._cssGlobalClasses
						),
					},
					children: getChildren(child.children),
				});
			});
			return children;
		};

		elementTree.children = getChildren(element.children);

		return elementTree;
	},

	getElementLabel: function (elementId) {
		let element = this._getElementById(elementId);
		return (
			element.label ||
			element.name.charAt(0).toUpperCase() + element.name.slice(1)
		);
	},

	getClassStringFromListItem: function (classListItem) {
		if (classListItem.firstElementChild.textContent.charAt(0) === ".")
			return classListItem.firstElementChild.textContent.slice(1);
		else return classListItem.firstElementChild.textContent;
	},

	getCurrentElementId: function () {
		const activeElement = this._getActiveElement();
		const cssId = activeElement.settings._cssId;
		if (cssId) {
			return "#" + cssId;
		} else {
			return "#brxe-" + activeElement.id;
		}
	},

	getCurrentInputOption: function (currentInput) {
		let optionString;
		optionString = currentInput.closest("[controlkey]")
			? currentInput.closest("[controlkey]").getAttribute("controlkey")
			: null;
		if (optionString && optionString != "") {
			return optionString;
		}
		return this.getCurrentCssProperty(currentInput);
	},

	//TODO: Refactor to use the BricksState for better perf
	getCurrentSelector: function () {
		let currentClass = document
			.querySelector(this.mainInputSelector)
			?.querySelector("input")
			?.getAttribute("value");
		if (currentClass) return currentClass;
		return document
			.querySelector(this.mainInputSelector)
			?.querySelector("input")
			?.getAttribute("placeholder"); //else return id
	},

	getUnusedClassListItems: function () {
		let listItems = [];

		//if closed open the popup
		if (!document.querySelector(".css-classes")) {
			//open class popup and try again
			/**@type {HTMLElement} */
			document.querySelector(this.mainInputSelector)?.click();
		}
		return (listItems = document.querySelectorAll(
			".css-classes ul:last-child li"
		));
	},

	getCurrentInput: function (eventTarget) {
		return eventTarget;
	},
};
