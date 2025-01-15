import "dotenv/config"

// Gets an auth token from your tenant
export async function getAuthToken(user: string, password: string): Promise<string> {
  const url = `${process.env.EMUAPI_URL}:${process.env.EMUAPI_PORT}/${process.env.EMUAPI_TENANT}/tokens`

  try {
    const response = await fetch(url, {
      method: "POST",
      body: JSON.stringify({ username: user, password: password }),
    })
    if (!response.ok) {
      throw new Error(`error getting auth token: ${response.status}; ${response.text()}`)
    }

    const authToken = response.headers.get("Authorization")
    if (!authToken) throw new Error("authToken not present in headers!")
    if (!authToken.includes("Bearer")) throw new Error("Bearer not including in Auth header!")

    return authToken

  } catch (error) {
    if (error instanceof Error) {
      console.error(error.message)
    } else {
      console.error("An unknown error occurred.")
    }
  }

  return ""
}
