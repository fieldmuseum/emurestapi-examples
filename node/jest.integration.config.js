/**
 * Runs only the tests that hit a real EMu REST API. They skip themselves unless
 * node/.env holds working credentials.
 *
 * Use `npm run test:integration`.
 */

/** @type {import('ts-jest').JestConfigWithTsJest} **/
module.exports = {
  ...require("./jest.config"),
  testPathIgnorePatterns: ["/node_modules/"],
  testMatch: ["**/*.integration.test.ts"],
  testTimeout: 30_000,
}
