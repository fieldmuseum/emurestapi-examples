import "dotenv/config"
import { getAuthToken } from "./auth"

test("Auth token contains Bearer", async () => {
  const authToken = await getAuthToken(process.env.EMUAPI_USER as string, process.env.EMUAPI_PASSWORD as string)

  expect(authToken).toMatch(/Bearer/)
})
