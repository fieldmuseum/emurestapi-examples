import "dotenv/config"
import { getAuthToken } from "../tokens/auth"
import type { EMuRecord } from "./types"

export async function getRecord(authToken: string, resource: string, irn: string, returnFields: string[]): Promise<EMuRecord> {
  if (authToken === "") throw new Error("no authToken provided!")
  if (resource === "") throw new Error("no resource provided!")
  if (irn === "") throw new Error("no irn provided!")

  let url = `${process.env.EMUAPI_URL}:${process.env.EMUAPI_PORT}/${process.env.EMUAPI_TENANT}/${resource}/${irn}`

  if (returnFields.length > 0) {
    url += `?select=${returnFields.join(",")}`
  }

  try {
    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Authorization": authToken,
        "Prefer": "representation=minimal",
      },
    })

    if (!response.ok) {
      throw new Error(`error getting record data: ${response.status}; ${response.text()}`)
    }

    const record = await response.json()
    return record

  } catch (error) {
    if (error instanceof Error) {
      console.error(error.message)
    } else {
      console.error("an unknown error occurred")
    }
  }

  throw new Error("failed to retrieve record")
}
