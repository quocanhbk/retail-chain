import axios from "axios"

// const baseURL = "https://149.28.148.73/bkrm/public/api"
export const baseURL = "http://localhost:8000/api"

export const fetcher = axios.create({ baseURL: baseURL, withCredentials: true })

fetcher.interceptors.response.use(
	res => res,
	err => {
		throw new Error(err.response.data.message)
	}
)

export default fetcher
