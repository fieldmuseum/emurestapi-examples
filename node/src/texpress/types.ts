// interface for Texpress Retrieve
// https://help.emu.axiell.com/emurestapi/3.1.3/04-Resources-Texpress.html
export interface EMuRecord {
  id: string
  version: number
  data: RecordData
}

interface RecordData {
  irn: {
    id: string
    '@controls': object
  }
  RightsAcknowledgeLocal?: string
  MulDescription?: string
}
