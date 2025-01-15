import "dotenv/config"
import { getAuthToken } from "../tokens/auth"
import { getRecord } from "./retrieve"

test("getRecord() returns record data", async () => {
  const authToken = await getAuthToken(process.env.EMUAPI_USER as string, process.env.EMUAPI_PASSWORD as string)
  const record = await getRecord(
    authToken,
    "emultimedia",
    "1581099",
    ["id", "data.RightsAcknowledgeLocal", "data.MulDescription"]
  )
  expect(record.data.irn.id).not.toEqual("")
  expect(record.data.RightsAcknowledgeLocal).not.toEqual("")
  expect(record.data.MulDescription).not.toEqual("")
})
