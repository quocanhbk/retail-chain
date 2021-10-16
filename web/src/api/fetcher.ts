import axios from "axios"

const baseURL = "https://149.28.148.73/bkrm/public/api"

const fetcher = axios.create({ baseURL })

export default fetcher
