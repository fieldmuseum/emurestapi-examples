/**
 * Hits a real EMu REST API. Skipped unless node/.env holds working credentials.
 *
 * Run just these with: npm run test:integration
 *
 * EMUAPI_TEST_IRN sets the record to read; it defaults to 1, which may well not
 * exist in your tenant. Point it at a record you know about.
 */

import { hasLiveCredentials } from "../testHelpers"
import { getAuthToken } from "../tokens/auth"
import { getRecord } from "./retrieve"

const describeLive = hasLiveCredentials() ? describe : describe.skip

describeLive("against a live EMu", () => {
  test("getRecord() returns record data", async () => {
    const authToken = await getAuthToken(
      process.env.EMUAPI_USER as string,
      process.env.EMUAPI_PASSWORD as string,
    )

    const irn = process.env.EMUAPI_TEST_IRN ?? "1"
    const returnFields = ["id", "data.RightsAcknowledgeLocal", "data.MulDescription"]

    const first = await getRecord(authToken, "emultimedia", irn, returnFields)

    expect(first.record.data.irn.id).not.toEqual("")
    expect(first.authToken).toMatch(/Bearer/)

    // The token that came back can be used for the next request, which is what
    // `renew` buys you. Here it reads the same record a second time.
    const second = await getRecord(first.authToken, "emultimedia", irn, returnFields)
    expect(second.record).toEqual(first.record)
  })
})
