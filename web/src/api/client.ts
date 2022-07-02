import { Api } from "./api"

const baseURL = "http://localhost:8000/api"

export const client = new Api({
  format: "json",
  baseURL,
  withCredentials: true
})

client.instance.interceptors.response.use(
  response => response,
  error => {
    throw new Error(error.response.data.message)
  }
)
