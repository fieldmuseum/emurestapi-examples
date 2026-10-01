// interface for Texpress Retrieve
// https://help.emu.axiell.com/emurestapi/latest/04-Resources-Texpress.html
export interface EMuRecord {
  id: string
  version: number
  data: RecordData
}

// Customize this to match your data
interface RecordData {
  irn: {
    id: string
    "@controls": object
  }
  RightsAcknowledgeLocal?: string
  MulDescription?: string
}

// What getRecord() hands back: the record, plus the token to use for the next
// request. See the note about `renew` in tokens/auth.ts.
export interface RetrieveResult {
  record: EMuRecord
  authToken: string
}

// interface for Texpress Search
// https://help.emu.axiell.com/emurestapi/latest/04-Resources-Texpress.html
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
    "@controls": object
  }
  NamFullName?: string
  NamBriefName?: string
}

// What search() hands back: the results, plus the token to use for the next
// request. See the note about `renew` in tokens/auth.ts.
export interface SearchResult {
  results: SearchResults
  authToken: string
}

// A single form parameter for a search request, e.g. { key: "limit", value: "5" }.
// Named SearchParam rather than FormData so it does not shadow the global
// FormData type.
export interface SearchParam {
  key: string
  value: string
}
