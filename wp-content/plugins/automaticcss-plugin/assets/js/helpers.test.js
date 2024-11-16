import { describe, it, expect, test } from "vitest";
import { toCamelCase } from "./helpers";

describe("Helper Util Test", () => {
	it("should transform string into camelcase", () => {
		const originalString = "background-color";

		const newString = toCamelCase(originalString);

		expect(newString).toBe("backgroundColor");
	});
});
