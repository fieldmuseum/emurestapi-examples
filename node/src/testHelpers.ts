/**
 * Helpers shared by the tests.
 *
 * The *.test.ts files double as the usage examples, and they run against a
 * mocked fetch so that a fresh clone can run them with no credentials and no
 * EMu installation. The live equivalents are the *.integration.test.ts files.
 */

import "dotenv/config"

/** Settings the examples read, pointing at a host that does not exist. */
export function useFakeEnv(): void {
  process.env.EMUAPI_URL = "http://emu.example.test"
  process.env.EMUAPI_PORT = "8080"
  process.env.EMUAPI_TENANT = "mymuseum"
}

export interface MockResponseInit {
  status?: number
  headers?: Record<string, string>
  /** An object is sent as JSON; a string is sent as-is. */
  body?: unknown
}

/**
 * Replaces global fetch with canned responses, handed out in call order.
 *
 * @returns The jest mock, so a test can assert on the requests that were sent.
 */
export function mockFetch(...responses: MockResponseInit[]): jest.SpyInstance {
  const spy = jest.spyOn(globalThis, "fetch")

  for (const response of responses) {
    const body =
      response.body === undefined
        ? null
        : typeof response.body === "string"
          ? response.body
          : JSON.stringify(response.body)

    spy.mockImplementationOnce(
      async () => new Response(body, { status: response.status ?? 200, headers: response.headers }),
    )
  }

  return spy
}

/** The arguments of the nth fetch call, as [url, init]. */
export function requestSent(spy: jest.SpyInstance, index = 0): { url: string; init: RequestInit } {
  const [url, init] = spy.mock.calls[index] as [string, RequestInit]

  return { url: url, init: init }
}

/** A header from a request the example sent. */
export function headerSent(spy: jest.SpyInstance, name: string, index = 0): string | undefined {
  const headers = requestSent(spy, index).init.headers as Record<string, string> | undefined

  return headers?.[name]
}

/**
 * Whether live credentials are configured. The integration tests skip
 * themselves when they are not; see node/.env.example.
 */
export function hasLiveCredentials(): boolean {
  return ["EMUAPI_URL", "EMUAPI_TENANT", "EMUAPI_USER", "EMUAPI_PASSWORD"].every((name) =>
    Boolean(process.env[name]),
  )
}
