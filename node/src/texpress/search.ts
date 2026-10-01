/**
 * PLEASE NOTE: the documentation for Search is unclear. This request must be a POST
 * with the method overridden to a GET, not a GET. See the Override doc below.
 * @link https://help.emu.axiell.com/emurestapi/latest/04-Resources-Texpress.html#search
 * @link https://help.emu.axiell.com/emurestapi/latest/05-Appendices-Override.html
 *
 * Query Syntax can be found in the appendices
 * @link https://help.emu.axiell.com/emurestapi/latest/05-Appendices-Query.html
 */

import { REQUEST_TIMEOUT_MS, httpError, tenantUrl } from "../config"
import type { SearchParam, SearchResult, SearchResults } from "./types"

/**
 * Searches an EMu module for records.
 *
 * @param authToken An Authorization header value from getAuthToken(), or from a
 *   previous response when the token was created with renew enabled
 * @param resource The EMu module to search, e.g. "eparties"
 * @param query Form parameters such as filter, sort and limit
 */
export async function search(
  authToken: string,
  resource: string,
  query: SearchParam[],
): Promise<SearchResult> {
  if (!authToken) throw new Error("no authToken provided!")
  if (!resource) throw new Error("no resource provided!")
  if (query.length === 0) throw new Error("no query provided!")

  const formData = new URLSearchParams()
  for (const q of query) {
    formData.append(q.key, q.value)
  }

  const response = await fetch(tenantUrl(resource), {
    method: "POST",
    headers: {
      Authorization: authToken,
      Prefer: "representation=minimal",
      // A search is a POST that the API treats as a GET. Without this header
      // the request is handled as a write.
      "X-HTTP-Method-Override": "GET",
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: formData.toString(),
    signal: AbortSignal.timeout(REQUEST_TIMEOUT_MS),
  })

  if (!response.ok) {
    throw await httpError("error searching resource", response)
  }

  // The shape of the JSON is not checked at runtime; SearchResults describes
  // what the API is documented to return. Customize it to match your own data.
  const results = (await response.json()) as SearchResults

  return {
    results: results,
    // Keep the token for the next request: when the token was created with
    // renew enabled, this header holds a fresh one with an updated expiry.
    authToken: response.headers.get("Authorization") ?? authToken,
  }
}
