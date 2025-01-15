import "dotenv/config"
import type { FormData, SearchResults } from "./types"

export async function search(authToken: string, resource: string, query: FormData[]): Promise<SearchResults> {
  if (authToken === "") throw new Error("no authToken provided!")
  if (resource === "") throw new Error("no resource provided!")
  if (query.length === 0) throw new Error("no query provided!")

  const url = `${process.env.EMUAPI_URL}:${process.env.EMUAPI_PORT}/${process.env.EMUAPI_TENANT}/${resource}`

  const formData = new URLSearchParams()
  for (const q of query) {
    formData.append(q.key, q.value)
  }

  // Appendices doc: https://help.emu.axiell.com/emurestapi/3.1.3/05-Appendices-Override.html
  try {
    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Authorization": authToken,
        "Prefer": "representation=minimal",
        "X-HTTP-Method-Override": "GET",
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: formData.toString(),
    })

    if (!response.ok) {
      throw new Error(`error getting searching resource: ${response.status}; ${response.text()}`)
    }

    const records = await response.json()
    return records

  } catch (error) {
    if (error instanceof Error) {
      console.error(error.message)
    } else {
      console.error("an unknown error occurred")
    }
  }

  throw new Error("failed to search resource")
}
