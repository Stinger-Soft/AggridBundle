module.exports = {
	root: true,
	extends: ['eslint:recommended', 'plugin:@typescript-eslint/recommended'],
	parserOptions: { "project": ["./tsconfig.json"] },
	parser: '@typescript-eslint/parser',
	plugins: ['@typescript-eslint'],
	"ignorePatterns": ["/node_modules/**/*.js", "**/vendor/**/*.js", "**/node_modules/**/*.ts", "**/vendor/**/*.ts"],


};