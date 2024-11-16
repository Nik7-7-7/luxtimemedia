export interface BuilderApi {
	name: string;
	mainInputSelector: string;
	mainPanelSelector: string;
	classInputSelector: string;
	saveClassSelector: string;

	findUnusedClass(classString: string): HTMLElement | null;
	sanitizeCssProperty(cssProperty: string, currentInput: any): string;
	getCurrentCssProperty(currentInput: HTMLInputElement): string;
	initHoverPreviewEventImplementation(initHoverPreview: () => void): void;
	executeOnClassListOpen(callback: () => void): void;
	getExcludedInputSelectors(): string;
	removeValuePreview(
		currentInput: HTMLInputElement,
		previewStyleElement: HTMLElement
	): void;
	setValue(value: string, currentInput: HTMLInputElement): void;
	displayValuePreview(
		value: string,
		currentInput: HTMLInputElement,
		previewStyleElement: HTMLElement
	): void;
	setUnitToNone(currentInput: HTMLInputElement): void;
	addStructurePanelButton(options: {
		icon: string;
		onClick: (e: Event, elementId: string) => void;
	}): void;
	changeLabelOfElement(label: string, elementId: string): boolean;
	copyStylesFromClass(sourceClass: string, targetClass: string): void;

	removeClass(className: string): void; //deprecated - use removeClassFromElement
	addClass(className: string): void; //deprecated - use addClassToElement

	renameClass(oldClassName: string, newClassName: string): void;
	rerenderStyles(): void;
	removeClassFromElement(className: string, elementId: string): void;
	deleteClassFromBuilder(className: string): void;
	addClassToElement(className: string, elementId: string): void;
	isClassAlreadyActive(className: string): boolean;
	getIframeDocument(): Document;
	getElementIdFromStructurePanelButton(eventTarget: HTMLElement): string;
	getActiveElementInternalId(): string;
	getElementTree(elementId: string): any;
	getElementLabel(elementId: string): string;
	getClassStringFromListItem(classListItem: HTMLElement): string;
	getCurrentElementId(): string;
	getCurrentInputOption(currentInput: HTMLInputElement): string;
	getCurrentSelector(): string;
	getUnusedClassListItems(): NodeListOf<Element>;
	getCurrentInput(eventTarget: HTMLElement): HTMLInputElement;
}
