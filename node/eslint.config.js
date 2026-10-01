const js = require("@eslint/js")
const tseslint = require("typescript-eslint")

const jestGlobals = {
  afterEach: "readonly",
  beforeEach: "readonly",
  describe: "readonly",
  expect: "readonly",
  jest: "readonly",
  test: "readonly",
}

const nodeGlobals = {
  console: "readonly",
  process: "readonly",
  module: "writable",
  require: "readonly",
  __dirname: "readonly",
}

module.exports = tseslint.config(
  { ignores: ["dist/**", "node_modules/**"] },
  js.configs.recommended,
  {
    // The examples themselves.
    files: ["src/**/*.ts"],
    extends: [...tseslint.configs.recommended],
    languageOptions: {
      parserOptions: { ecmaVersion: 2022, sourceType: "module" },
      globals: nodeGlobals,
    },
  },
  {
    files: ["src/**/*.test.ts", "src/testHelpers.ts"],
    languageOptions: { globals: jestGlobals },
  },
  {
    // Build and test configuration, which is CommonJS.
    files: ["*.config.js"],
    languageOptions: { sourceType: "commonjs", globals: nodeGlobals },
  },
)
