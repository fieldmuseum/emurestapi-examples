/**
 * Usage example for getRecord(), exercised against a mocked fetch so that it
 * runs without a live EMu installation. The equivalent test against a real
 * tenant is in retrieve.integration.test.ts.
 */

import { headerSent, mockFetch, requestSent, useFakeEnv } from "../testHelpers"
import { getRecord } from "./retrieve"

beforeEach(useFakeEnv)
afterEach(() => jest.restoreAllMocks())

const returnFields = ["id", "data.RightsAcknowledgeLocal", "data.MulDescription"]

test("getRecord() returns record data", async () => {
  const body = {
    id: "1581099",
    version: 1,
    data: {
      irn: { id: "1581099", "@controls": {} },
      RightsAcknowledgeLocal: "Field Museum",
      MulDescription: "A photograph of a specimen",
    },
  }

  const fetchMock = mockFetch({ headers: { Authorization: "Bearer refreshed" }, body: body })

  const { record, authToken } = await getRecord(
    "Bearer original",
    "emultimedia",
    "1581099",
    returnFields,
  )

  expect(record.data.irn.id).toBe("1581099")
  expect(record.data.RightsAcknowledgeLocal).toBe("Field Museum")
  expect(record.data.MulDescription).toBe("A photograph of a specimen")

  // The refreshed token from the response is handed back for the next request.
  expect(authToken).toBe("Bearer refreshed")

  // Check the request the example actually sent.
  const { url, init } = requestSent(fetchMock)
  expect(init.method).toBe("GET")
  expect(headerSent(fetchMock, "Authorization")).toBe("Bearer original")
  expect(url).toBe(
    "http://emu.example.test:8080/mymuseum/emultimedia/1581099" +
      "?select=id,data.RightsAcknowledgeLocal,data.MulDescription",
  )
})

test("getRecord() asks for the whole record when no fields are given", async () => {
  const fetchMock = mockFetch({ body: { id: "1" } })

  await getRecord("Bearer abc", "emultimedia", "1", [])

  expect(requestSent(fetchMock).url).toBe("http://emu.example.test:8080/mymuseum/emultimedia/1")
})

test("getRecord() keeps the current token when the response does not refresh it", async () => {
  mockFetch({ body: { id: "1" } })

  const { authToken } = await getRecord("Bearer original", "emultimedia", "1", [])

  expect(authToken).toBe("Bearer original")
})

test("getRecord() rejects missing arguments", async () => {
  await expect(getRecord("", "emultimedia", "1", [])).rejects.toThrow("no authToken provided!")
  await expect(getRecord("Bearer abc", "", "1", [])).rejects.toThrow("no resource provided!")
  await expect(getRecord("Bearer abc", "emultimedia", "", [])).rejects.toThrow("no irn provided!")
})

test("getRecord() reports the status and body of a failed request", async () => {
  mockFetch({ status: 404, body: '{"error":"not found"}' })

  await expect(getRecord("Bearer abc", "emultimedia", "999999999", [])).rejects.toThrow(
    /error getting record data: HTTP 404.*not found/,
  )
})
