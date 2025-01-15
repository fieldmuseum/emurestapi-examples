# EMu REST API examples

## PHP

Usage examples can be found in the `php/tests` directory. Use Pest to run a test.
e.g. from the `php` directory: `./vendor/bin/pest tests/Unit/RetrieveTest.php`

Not implemented yet

- Insert (no key), Insert/Replace
- Edit
- Delete

### Authorization

Authorization is handled by JWT. Take a look at the `php/src/Tokens/Auth.php` file
for more info.

### Retrieve

Gets a single record from an EMu module.
Take a look at the function in the `php/src/Texpress/Retrieve.php` file.

### Search

Searches an EMu module for records. Look at the function in the `php/src/Texpress/Search.php` file.

## Node

Node.js examples, using Typescript, can be found in the `node/src` directories.
Use Jest via `npx jest` from the `node` directory. Usage examples can be found in each
`*.test.ts` file located alongside each code file, similar to the PHP examples.

Not implemented yet

- Insert (no key), Insert/Replace
- Edit
- Delete
