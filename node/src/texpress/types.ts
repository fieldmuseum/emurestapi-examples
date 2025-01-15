// interface for Texpress Retrieve
// https://help.emu.axiell.com/emurestapi/3.1.3/04-Resources-Texpress.html
export interface EMuRecord {
  id: string
  version: number
  data: RecordData
}

// Customize this to match your data
interface RecordData {
  irn: {
    id: string
    '@controls': object
  }
  RightsAcknowledgeLocal?: string
  MulDescription?: string
}

// interface for Texpress Search
// https://help.emu.axiell.com/emurestapi/3.1.3/04-Resources-Texpress.html
export interface SearchResults {
  hits: number
  matches: SearchMatch[]
}

interface SearchMatch {
  id: string
  version: number
  data: SearchMatchData
}

// Customize this to match your data
interface SearchMatchData {
  irn: {
    id: string
    '@controls': object
  }
  NamFullName?: string
  NamBriefName?: string
}

export interface FormData {
  key: string
  value: string
}
