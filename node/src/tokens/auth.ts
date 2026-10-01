/**
 * This file shows how to get a JWT for authentication to the EMu REST API.
 *
 * A key thing to note is that when you do a POST request to get the Bearer token,
 * you'll need to get the token from the "Authorization" response header. Don't look
 * in the response body.
 *
 * Check out your options for the auth token here, specifically the timeout and renew
 * options. If renew is set to true, then new auth tokens will be generated (with
 * updated expiry time) with each request and you can just pass the new Authorization
 * header from request to request. See retrieve.ts and search.ts for how that is done.
 * @link https://help.emu.axiell.com/emurestapi/latest/04-Resources-Tokens.html#username-password
 */

import { REQUEST_TIMEOUT_MS, httpError, tenantUrl } from "../config"

/**
 * Gets an auth token from your tenant.
 *
 * @param user EMu username
 * @param password EMu password
 * @param timeout Idle/elapsed time in minutes before expiry of the created token
 * @param renew Whether new tokens should be generated with each request
 *
 * @returns The value of the Authorization response header, e.g. "Bearer eyJ..."
 */
export async function getAuthToken(
  user: string,
  password: string,
  timeout: number = 30,
  renew: boolean = true,
): Promise<string> {
  if (!user) throw new Error("no username provided!")
  if (!password) throw new Error("no password provided!")

  const response = await fetch(tenantUrl("tokens"), {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Prefer: "representation=minimal",
    },
    body: JSON.stringify({ username: user, password: password, timeout: timeout, renew: renew }),
    signal: AbortSignal.timeout(REQUEST_TIMEOUT_MS),
  })

  if (!response.ok) {
    throw await httpError("error getting auth token", response)
  }

  // The token is in the response header, not the body.
  const authToken = response.headers.get("Authorization")
  if (!authToken) throw new Error("Authorization header missing from the token response!")
  if (!authToken.includes("Bearer")) {
    throw new Error(`expected a Bearer token in the Authorization header, got: ${authToken}`)
  }

  return authToken
}
