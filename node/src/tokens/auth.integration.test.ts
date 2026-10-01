/**
 * Hits a real EMu REST API. Skipped unless node/.env holds working credentials.
 *
 * Run just these with: npm run test:integration
 */

import { hasLiveCredentials } from "../testHelpers"
import { getAuthToken } from "./auth"

const describeLive = hasLiveCredentials() ? describe : describe.skip

describeLive("against a live EMu", () => {
  test("Auth token contains Bearer", async () => {
    const authToken = await getAuthToken(
      process.env.EMUAPI_USER as string,
      process.env.EMUAPI_PASSWORD as string,
    )

    expect(authToken).toMatch(/Bearer/)
  })
})
