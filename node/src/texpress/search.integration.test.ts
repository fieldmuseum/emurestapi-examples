/**
 * Hits a real EMu REST API. Skipped unless node/.env holds working credentials.
 *
 * Run just these with: npm run test:integration
 */

import { hasLiveCredentials } from "../testHelpers"
import { getAuthToken } from "../tokens/auth"
import type { SearchParam } from "./types"
import { search } from "./search"

const describeLive = hasLiveCredentials() ? describe : describe.skip

describeLive("against a live EMu", () => {
  test("search() queries a resource for record data", async () => {
    const authToken = await getAuthToken(
      process.env.EMUAPI_USER as string,
      process.env.EMUAPI_PASSWORD as string,
    )

    const query: SearchParam[] = [
      { key: "filter", value: '{"AND":[{"data.NamLast":{"exact":{"value": "Smith"}}}]}' },
      { key: "sort", value: '[{"data.NamFirst":{"order":"asc"}}]' },
      { key: "limit", value: "5" },
    ]

    const { results, authToken: nextToken } = await search(authToken, "eparties", query)

    expect(results.hits).toBeGreaterThan(0)
    expect(results.matches[0].data.NamBriefName).not.toEqual("")
    expect(results.matches[0].data.NamFullName).not.toEqual("")
    expect(nextToken).toMatch(/Bearer/)
  })
})
