import "dotenv/config"
import type { FormData } from "./types"
import { getAuthToken } from "../tokens/auth"
import { search } from "./search"

test("search() queries a resource for record data", async () => {
  const authToken = await getAuthToken(process.env.EMUAPI_USER as string, process.env.EMUAPI_PASSWORD as string)

  const query: FormData[] = [
    { key: "filter", value: '{"AND":[{"data.NamLast":{"exact":{"value": "Smith"}}}]}' },
    { key: "sort", value: '[{"data.NamFirst":{"order":"asc"}}]' },
    { key: "limit", value: '5' },
  ]
  const searchResults = await search(authToken, "eparties", query)

  expect(searchResults.hits).toBeGreaterThan(0)
  expect(searchResults.matches[0].data.NamBriefName).not.toEqual("")
  expect(searchResults.matches[0].data.NamFullName).not.toEqual("")
})
