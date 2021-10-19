import axios from "axios"

const baseURL = "https://149.28.148.73/bkrm/public/api"
const localBaseURL = "http://localhost:8000/api"
const fetcher = axios.create({ baseURL: localBaseURL })

export default fetcher
