/**
 * This file shows how to get an individual record.
 * @link https://help.emu.axiell.com/emurestapi/latest/04-Resources-Texpress.html#retrieve
 */

import { REQUEST_TIMEOUT_MS, httpError, tenantUrl } from "../config"
import type { EMuRecord, RetrieveResult } from "./types"

/**
 * Retrieves a single record from an EMu module.
 *
 * @param authToken An Authorization header value from getAuthToken(), or from a
 *   previous response when the token was created with renew enabled
 * @param resource The EMu module to read from, e.g. "ecatalogue"
 * @param irn The record's internal record number
 * @param returnFields Fields to select, e.g. ["id", "data.MulDescription"].
 *   Pass an empty array for the whole record.
 */
export async function getRecord(
  authToken: string,
  resource: string,
  irn: string,
  returnFields: string[],
): Promise<RetrieveResult> {
  if (!authToken) throw new Error("no authToken provided!")
  if (!resource) throw new Error("no resource provided!")
  if (!irn) throw new Error("no irn provided!")

  let url = tenantUrl(resource, irn)
  if (returnFields.length > 0) {
    // Encode each field name, but leave the separating commas intact.
    url += `?select=${returnFields.map(encodeURIComponent).join(",")}`
  }

  const response = await fetch(url, {
    method: "GET",
    headers: {
      Authorization: authToken,
      Prefer: "representation=minimal",
    },
    signal: AbortSignal.timeout(REQUEST_TIMEOUT_MS),
  })

  if (!response.ok) {
    throw await httpError("error getting record data", response)
  }

  // The shape of the JSON is not checked at runtime; EMuRecord describes what
  // the API is documented to return. Customize it to match your own data.
  const record = (await response.json()) as EMuRecord

  return {
    record: record,
    // Keep the token for the next request: when the token was created with
    // renew enabled, this header holds a fresh one with an updated expiry.
    authToken: response.headers.get("Authorization") ?? authToken,
  }
}
