import { LoginInput, LoginOutput } from "@@types"
import fetcher from "../fetcher"

export const login = async (input: LoginInput): Promise<LoginOutput> => {
	const { data } = await fetcher.post("/auth/login", input)
	return data
}

export const me = async (): Promise<LoginOutput> => {
	const { data } = await fetcher.get("/me")
	return data
}

export const logout = async (): Promise<void> => {
	await fetcher.post("/auth/logout")
}
