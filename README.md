# EMu REST API examples

Working examples of talking to [Axiell's EMu REST API][docs], in PHP and in
TypeScript. Each example is a small, self-contained function with the API's
quirks documented inline; the tests alongside them double as usage examples.

[docs]: https://help.emu.axiell.com/emurestapi/latest/

## What's covered

| Operation | PHP | Node |
| --- | --- | --- |
| Authorization (JWT) | [`src/Tokens/Auth.php`](php/src/Tokens/Auth.php) | [`src/tokens/auth.ts`](node/src/tokens/auth.ts) |
| Retrieve a single record | [`src/Texpress/Retrieve.php`](php/src/Texpress/Retrieve.php) | [`src/texpress/retrieve.ts`](node/src/texpress/retrieve.ts) |
| Search a module | [`src/Texpress/Search.php`](php/src/Texpress/Search.php) | [`src/texpress/search.ts`](node/src/texpress/search.ts) |

Not implemented yet, in either language:

- Insert (no key), Insert/Replace
- Edit
- Delete

## Three things worth knowing

These cost us time, so they are called out here as well as in the code.

1. **The auth token comes back in a response header, not the response body.**
   Read `Authorization` off the response to the `POST /{tenant}/tokens` request.

2. **A search is a POST, not a GET.** The [Texpress documentation][search] is
   unclear on this. Send a `POST` with `X-HTTP-Method-Override: GET` and a
   form-encoded body; a plain `GET` will not do what you want. See the
   [Override appendix][override].

3. **Tokens renew themselves.** When a token is created with `renew` enabled
   (the default in these examples), every subsequent response carries a fresh
   `Authorization` header with an updated expiry. Pass it to the next request
   instead of authenticating again — the retrieve and search examples return it
   alongside the data for exactly this reason.

[search]: https://help.emu.axiell.com/emurestapi/latest/04-Resources-Texpress.html#search
[override]: https://help.emu.axiell.com/emurestapi/latest/05-Appendices-Override.html

## Setup

Both examples read their settings from a `.env` file in their own directory.

```bash
cp php/.env.example php/.env     # then fill it in
cp node/.env.example node/.env   # then fill it in
```

`EMUAPI_PORT` can be left empty if your API is served on the default port.
Credentials are only needed for the integration tests; see below.

## PHP

Requires PHP 8.2 or newer and [Composer](https://getcomposer.org/).

```bash
cd php
composer install
composer test
```

| Command | What it does |
| --- | --- |
| `composer test` | Runs the mocked tests. No credentials needed. |
| `composer test:integration` | Runs the tests that hit a real EMu. |
| `composer test:all` | Both. |
| `composer lint` | Checks formatting with [Pint](https://laravel.com/docs/pint). |
| `composer lint:fix` | Fixes formatting. |

## Node

Requires Node 18 or newer, for native `fetch` and `AbortSignal.timeout`.

```bash
cd node
npm install
npm test
```

| Command | What it does |
| --- | --- |
| `npm test` | Runs the mocked tests. No credentials needed. |
| `npm run test:one -- <path> [-t <name>]` | Runs one file, or one test, with each test name printed. |
| `npm run test:integration` | Runs the tests that hit a real EMu. |
| `npm run test:one:integration -- <path> [-t <name>]` | Same as `test:one`, for the integration tests. |
| `npm run example` | Runs [`src/index.ts`](node/src/index.ts) end to end against your tenant. |
| `npm run typecheck` | Typechecks without emitting. |
| `npm run lint` | Lints with ESLint. |
| `npm run format` / `npm run format:fix` | Checks / applies Prettier formatting. |

## How the tests are organised

The tests are the usage examples, so they are written to be read:

- **`*.test.ts` and `tests/Unit`** mock the HTTP layer. They run on a fresh
  clone with no credentials and no EMu installation, and they assert on the
  exact request each example sends — method, URL, headers and body — which is
  usually the part you want to copy. These are what CI runs.

- **`*.integration.test.ts` and `tests/Integration`** send real requests. They
  skip themselves unless `.env` holds working credentials, so they are safe to
  leave in place. `EMUAPI_TEST_IRN` points the retrieve test at a record that
  exists in your tenant.

### Running one test

In `node`, `test:one` takes a file and an optional test name, and prints each
test name as it runs:

```bash
npm run test:one -- src/tokens/auth.test.ts                          # one file
npm run test:one -- src/tokens/auth.test.ts -t "omits the port"      # one test
```

The path is a regex matched against the file path rather than a literal path, so
`npm run test:one -- tokens` runs everything under that directory. `-t` matches
on a substring of the test name; the other tests in the file are reported as
skipped. Note that `test:one` uses the default config, so it will not find an
integration test; those have their own script:

```bash
npm run test:one:integration -- src/tokens/auth.integration.test.ts
```

In `php`, Pest takes a path, and `--filter` narrows to one test:

```bash
./vendor/bin/pest tests/Unit/AuthTest.php
./vendor/bin/pest tests/Unit/AuthTest.php --filter="rejects missing credentials"
```

## License

MIT. See [LICENSE](LICENSE).
