/**
 * Shared configuration for the examples: where your EMu REST API lives, and the
 * credentials used to authenticate against it. Every example reads its settings
 * from here so that the example files themselves stay focused on the parts that
 * are specific to the EMu API.
 *
 * Copy `.env.example` to `.env` and fill it in before running anything.
 */

import "dotenv/config"

/**
 * How long to wait on a single request before giving up.
 *
 * `fetch` has no timeout of its own, so without this a request to an
 * unreachable EMu host hangs forever.
 */
export const REQUEST_TIMEOUT_MS = 15_000

/** Reads a required environment variable, failing loudly when it is missing. */
function required(name: string): string {
  const value = process.env[name]
  if (!value) {
    throw new Error(`missing ${name}! check your .env file/environment`)
  }
  return value
}

/**
 * The base URL of the API, with the port appended only when one is configured.
 *
 * EMu installations behind a reverse proxy are often served on the default
 * port, in which case EMUAPI_PORT is left empty.
 */
export function baseUrl(): string {
  const url = required("EMUAPI_URL")
  const port = process.env.EMUAPI_PORT

  return port ? `${url}:${port}` : url
}

/** Tenant is the EMu machine name for your institution. */
export function tenant(): string {
  return required("EMUAPI_TENANT")
}

/**
 * Builds a URL under your tenant, e.g. tenantUrl("ecatalogue", "1") =>
 * "https://emu.example.com:8080/mymuseum/ecatalogue/1"
 */
export function tenantUrl(...segments: string[]): string {
  const path = [tenant(), ...segments].map(encodeURIComponent).join("/")

  return `${baseUrl()}/${path}`
}

/** The username/password used by the token examples. */
export function credentials(): { user: string; password: string } {
  return {
    user: required("EMUAPI_USER"),
    password: required("EMUAPI_PASSWORD"),
  }
}

/**
 * Turns a failed response into an error that actually says what went wrong.
 *
 * Note the `await` on `response.text()`: it returns a promise, so interpolating
 * it directly into a template string yields "[object Promise]".
 */
export async function httpError(context: string, response: Response): Promise<Error> {
  const body = await response.text().catch(() => "<could not read response body>")

  return new Error(`${context}: HTTP ${response.status} ${response.statusText}; ${body}`)
}
