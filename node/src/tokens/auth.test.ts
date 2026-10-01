/**
 * Usage example for getAuthToken(), exercised against a mocked fetch so that it
 * runs without a live EMu installation. The equivalent test against a real
 * tenant is in auth.integration.test.ts.
 */

import { headerSent, mockFetch, requestSent, useFakeEnv } from "../testHelpers"
import { getAuthToken } from "./auth"

beforeEach(useFakeEnv)
afterEach(() => jest.restoreAllMocks())

test("getAuthToken() reads the Bearer token from the Authorization response header", async () => {
  // The token comes back in a header, not in the body.
  const fetchMock = mockFetch({ headers: { Authorization: "Bearer header.payload.signature" } })

  const authToken = await getAuthToken("username", "password")

  expect(authToken).toBe("Bearer header.payload.signature")

  // Check the request the example actually sent.
  const { url, init } = requestSent(fetchMock)
  expect(url).toBe("http://emu.example.test:8080/mymuseum/tokens")
  expect(init.method).toBe("POST")
  expect(headerSent(fetchMock, "Content-Type")).toBe("application/json")
  expect(JSON.parse(init.body as string)).toEqual({
    username: "username",
    password: "password",
    timeout: 30,
    renew: true,
  })
})

test("getAuthToken() passes the timeout and renew options through", async () => {
  const fetchMock = mockFetch({ headers: { Authorization: "Bearer abc" } })

  await getAuthToken("username", "password", 5, false)

  expect(JSON.parse(requestSent(fetchMock).init.body as string)).toMatchObject({
    timeout: 5,
    renew: false,
  })
})

test("getAuthToken() rejects missing credentials", async () => {
  await expect(getAuthToken("", "password")).rejects.toThrow("no username provided!")
  await expect(getAuthToken("username", "")).rejects.toThrow("no password provided!")
})

test("getAuthToken() complains when the tenant is not configured", async () => {
  process.env.EMUAPI_TENANT = ""

  await expect(getAuthToken("username", "password")).rejects.toThrow("missing EMUAPI_TENANT!")
})

test("getAuthToken() reports the status and body of a failed request", async () => {
  mockFetch({ status: 401, body: '{"error":"invalid credentials"}' })

  await expect(getAuthToken("username", "wrong-password")).rejects.toThrow(
    /error getting auth token: HTTP 401.*invalid credentials/,
  )
})

test("getAuthToken() complains when the response has no Authorization header", async () => {
  mockFetch({})

  await expect(getAuthToken("username", "password")).rejects.toThrow(
    "Authorization header missing from the token response!",
  )
})

test("getAuthToken() omits the port when none is configured", async () => {
  delete process.env.EMUAPI_PORT
  const fetchMock = mockFetch({ headers: { Authorization: "Bearer abc" } })

  await getAuthToken("username", "password")

  expect(requestSent(fetchMock).url).toBe("http://emu.example.test/mymuseum/tokens")
})
