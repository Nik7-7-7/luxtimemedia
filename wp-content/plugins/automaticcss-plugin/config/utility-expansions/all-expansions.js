import colorExpansions from "./color-expansions.json" assert { type: "json" };
import gridExpansions from "./grid-expansions.json" assert { type: "json" };
import ipsumExpansions from "./ipsum-expansions.json" assert { type: "json" };
import mediaQueryExpansions from "./media-query-expansions.json" assert { type: "json" };
import miscellaneousExpansions from "./miscellaneous-expansions.json" assert { type: "json" };
import jsExpansions from "./js-expansions.json" assert { type: "json" };

// !! To add a new expansion json, import it and add it to the "jsonFiles" array !!

const jsonFiles = [
	colorExpansions,
	gridExpansions,
	ipsumExpansions,
	mediaQueryExpansions,
	miscellaneousExpansions,
	jsExpansions
];

// !! you can also modify the global expansion settings by modifying "expansionSettings"

export const expansionSettings = {
	startCharacter: "@", // The char the expansion needs to start with to be recognized
	fallbackToStylesheet: false, // If set to true and definition is not found -> try to pull it from the stylesheet instead
};

// Compute combined json from all the given jsoin files
export const [expansionsJson, expansionsCheatsheet] = (() => {
	const resultJson = { expansions: {}, wrappers: {} };
	const resultCheatsheet = [{
		label: "Expansions",
		children: [],
	}];

	jsonFiles.forEach((jsonFile) => {
		// add expansions to result
		if (jsonFile.expansions) {
			resultJson.expansions = {
				...resultJson.expansions,
				...jsonFile.expansions,
			};
		}

		// add wrappers to result
		if (jsonFile.wrappers) {
			resultJson.wrappers = { ...resultJson.wrappers, ...jsonFile.wrappers };
		}

		// add cheatsheet to result
		resultCheatsheet[0].children.push({
			label: jsonFile.label,
			children: Object.entries(jsonFile.expansions).map(([key, value]) => {
				return {
					label: key,
					children: [],
					properties: {
						...value,
						type: "expansion",
					},
				};
			}),
		});
	});

	return [resultJson, resultCheatsheet];
})();

// After adding a new expansion, run npx rollup -c to make it active.
