/** @type {import('ts-jest').JestConfigWithTsJest} **/
module.exports = {
  testEnvironment: "node",
  transform: {
    "^.+\\.tsx?$": ["ts-jest", {}],
  },
  // The *.integration.test.ts files need live credentials and a reachable EMu,
  // so they are not part of the default run. Use `npm run test:integration`.
  testPathIgnorePatterns: ["/node_modules/", "\\.integration\\.test\\.ts$"],
}
