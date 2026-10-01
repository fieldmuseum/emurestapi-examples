/**
 * Usage example for search(), exercised against a mocked fetch so that it runs
 * without a live EMu installation. The equivalent test against a real tenant is
 * in search.integration.test.ts.
 */

import { headerSent, mockFetch, requestSent, useFakeEnv } from "../testHelpers"
import type { SearchParam } from "./types"
import { search } from "./search"

beforeEach(useFakeEnv)
afterEach(() => jest.restoreAllMocks())

const query: SearchParam[] = [
  { key: "filter", value: '{"AND":[{"data.NamLast":{"exact":{"value": "Smith"}}}]}' },
  { key: "sort", value: '[{"data.NamFirst":{"order":"asc"}}]' },
  { key: "limit", value: "5" },
]

test("search() queries a resource for record data", async () => {
  const body = {
    hits: 2,
    matches: [
      {
        id: "1",
        version: 1,
        data: {
          irn: { id: "1", "@controls": {} },
          NamFullName: "Alice Smith",
          NamBriefName: "A Smith",
        },
      },
      {
        id: "2",
        version: 1,
        data: {
          irn: { id: "2", "@controls": {} },
          NamFullName: "Bob Smith",
          NamBriefName: "B Smith",
        },
      },
    ],
  }

  const fetchMock = mockFetch({ headers: { Authorization: "Bearer refreshed" }, body: body })

  const { results, authToken } = await search("Bearer original", "eparties", query)

  expect(results.hits).toBe(2)
  expect(results.matches[0].data.NamFullName).toBe("Alice Smith")
  expect(results.matches[0].data.NamBriefName).toBe("A Smith")

  // The refreshed token from the response is handed back for the next request.
  expect(authToken).toBe("Bearer refreshed")

  // A search is a POST that the API is told to treat as a GET. This is the part
  // the official documentation does not make clear.
  const { url, init } = requestSent(fetchMock)
  expect(init.method).toBe("POST")
  expect(headerSent(fetchMock, "X-HTTP-Method-Override")).toBe("GET")
  expect(headerSent(fetchMock, "Content-Type")).toBe("application/x-www-form-urlencoded")
  expect(url).toBe("http://emu.example.test:8080/mymuseum/eparties")

  const sentBody = new URLSearchParams(init.body as string)
  expect(sentBody.get("filter")).toBe(query[0].value)
  expect(sentBody.get("sort")).toBe(query[1].value)
  expect(sentBody.get("limit")).toBe("5")
})

test("search() rejects missing arguments", async () => {
  await expect(search("", "eparties", query)).rejects.toThrow("no authToken provided!")
  await expect(search("Bearer abc", "", query)).rejects.toThrow("no resource provided!")
  await expect(search("Bearer abc", "eparties", [])).rejects.toThrow("no query provided!")
})

test("search() reports the status and body of a failed request", async () => {
  mockFetch({ status: 400, body: '{"error":"bad query"}' })

  await expect(search("Bearer abc", "eparties", query)).rejects.toThrow(
    /error searching resource: HTTP 400.*bad query/,
  )
})
