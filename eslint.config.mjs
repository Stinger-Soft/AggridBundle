import love from "eslint-config-love";
import pluginReact from "eslint-plugin-react";

export default [
	{
		ignores: ["node_modules/**", "dist/**", "vendor/**"],
	},
	{
		...love,
		files: ["ts/**/*.{ts,tsx}"],
		settings: {
			react: {
				version: "detect",
			},
		},
		rules: {
			...love.rules,
			// This codebase is a dynamic ag-grid wrapper using registry patterns with
			// string-keyed lookups and dynamic configuration objects. The `any` type is
			// fundamental to its architecture, so we relax unsafe-* rules accordingly.
			"@typescript-eslint/no-unsafe-assignment": "off",
			"@typescript-eslint/no-unsafe-member-access": "off",
			"@typescript-eslint/no-unsafe-call": "off",
			"@typescript-eslint/no-unsafe-argument": "off",
			"@typescript-eslint/no-unsafe-return": "off",
			"@typescript-eslint/no-unsafe-type-assertion": "off",
			"@typescript-eslint/no-explicit-any": "warn",

			// Dynamic configuration objects require flexible boolean checks
			"@typescript-eslint/strict-boolean-expressions": "off",

			// Registry classes only hold static methods - this is by design
			"@typescript-eslint/no-extraneous-class": "off",

			// ag-grid callbacks frequently receive many parameters
			"@typescript-eslint/max-params": "off",

			// Magic numbers like 0, 1, 50, 100, 500 are clear in context (array indices, timeouts, percentages)
			"@typescript-eslint/no-magic-numbers": "off",

			// Allow console for warnings/debug in a library
			"no-console": "off",

			// hasOwnProperty is used extensively and safely here
			"no-prototype-builtins": "off",

			// Configuration objects are built up by mutation
			"no-param-reassign": "off",

			// Allow == for loose null/undefined checks common in ag-grid patterns
			"eqeqeq": ["error", "smart"],

			// The library uses `var that = this` pattern in several jQuery callbacks
			"@typescript-eslint/no-this-alias": "off",

			// Allow var declarations in legacy code
			"no-var": "warn",

			// Complexity is acceptable for grid configuration methods
			"complexity": "off",

			// Return types can be inferred in many utility functions
			"@typescript-eslint/explicit-function-return-type": "off",

			// Prefer destructuring is too aggressive for this codebase
			"@typescript-eslint/prefer-destructuring": "off",

			// Allow nullish coalescing but don't require it everywhere
			"@typescript-eslint/prefer-nullish-coalescing": "warn",

			// Some conditions are necessary for defensive coding
			"@typescript-eslint/no-unnecessary-condition": "warn",

			// Allow non-null assertions in ag-grid context where we know values exist
			"@typescript-eslint/no-non-null-assertion": "warn",

			// Allow @ts-ignore for ag-grid API quirks
			"@typescript-eslint/ban-ts-comment": "warn",

			// Allow deprecated ag-grid API usage (will be updated on next major version)
			"@typescript-eslint/no-deprecated": "warn",

			// Allow unbound methods in ag-grid event handlers
			"@typescript-eslint/unbound-method": "off",

			// Allow floating promises for fire-and-forget patterns (e.g. dynamic imports)
			"@typescript-eslint/no-floating-promises": "warn",

			// eslint-disable comments don't need descriptions in this codebase
			"@eslint-community/eslint-comments/require-description": "off",

			// Allow ++ operator in loops
			"no-plusplus": "off",

			// File length limit is too restrictive for the main StingerSoftAggrid class
			"max-lines": "off",

			// init-declarations: allow uninitialized declarations
			"@typescript-eslint/init-declarations": "off",

			// class-methods-use-this: static utility methods in classes are fine
			"@typescript-eslint/class-methods-use-this": "off",

			// Allow new for side effects (e.g. isConstructor check)
			"no-new": "off",

			// Constructor names from dynamic lookups may be lowercase
			"new-cap": "off",

			// Allow require() for dynamic imports (moment locales)
			"@typescript-eslint/no-require-imports": "off",
		},
	},
	{
		files: ["ts/**/*.{tsx}"],
		plugins: {
			react: pluginReact,
		},
		rules: {
			...pluginReact.configs.recommended.rules,
			"react/react-in-jsx-scope": "off",
		},
	},
];
