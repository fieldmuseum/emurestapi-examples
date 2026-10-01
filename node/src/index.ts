/**
 * A runnable end-to-end example: get a token, search a module, then read one of
 * the records that came back, passing the refreshed token along as you go.
 *
 * Copy .env.example to .env and fill it in, then run: npm run example
 */

import { credentials } from "./config"
import { getRecord } from "./texpress/retrieve"
import { search } from "./texpress/search"
import type { SearchParam } from "./texpress/types"
import { getAuthToken } from "./tokens/auth"

async function main(): Promise<void> {
  const { user, password } = credentials()

  // 1. Authenticate. `renew` is on by default, so every response from here on
  //    carries a fresh token with an updated expiry.
  let authToken = await getAuthToken(user, password)
  console.log("got a token:", `${authToken.slice(0, 20)}...`)

  // 2. Search a module. Query syntax is in the appendices:
  //    https://help.emu.axiell.com/emurestapi/latest/05-Appendices-Query.html
  const query: SearchParam[] = [
    { key: "filter", value: '{"AND":[{"data.NamLast":{"exact":{"value": "Smith"}}}]}' },
    { key: "sort", value: '[{"data.NamFirst":{"order":"asc"}}]' },
    { key: "limit", value: "5" },
  ]

  const found = await search(authToken, "eparties", query)
  authToken = found.authToken
  console.log(`search matched ${found.results.hits} record(s)`)

  const firstMatch = found.results.matches[0]
  if (!firstMatch) {
    console.log("nothing to retrieve; try a different filter")
    return
  }

  // 3. Retrieve one record in full, reusing the token the search handed back.
  const retrieved = await getRecord(authToken, "eparties", firstMatch.data.irn.id, [
    "id",
    "data.NamFullName",
  ])
  console.log("retrieved:", JSON.stringify(retrieved.record, null, 2))
}

main().catch((error: unknown) => {
  if (error instanceof Error) {
    console.error(error.message)
    // fetch reports connection problems as a bare "fetch failed"; the useful
    // detail is on the cause.
    if (error.cause !== undefined) console.error("  cause:", error.cause)
  } else {
    console.error(error)
  }
  process.exitCode = 1
})
