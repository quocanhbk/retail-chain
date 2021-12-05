import { LoginInput, LoginOutput, RegisterInput, RegisterOutputAdmin } from "@@types"
import { adminFetcher } from "../fetcher"

export const register = async (input: RegisterInput): Promise<RegisterOutputAdmin> => {
	const { data } = await adminFetcher.post("/auth/register", input)
	return data
}

export const loginAsAdmin = async (input: LoginInput): Promise<LoginOutput> => {
	const { data } = await adminFetcher.post("/auth/login", input)
	return data
}

export const logoutAsAdmin = async (): Promise<void> => {
	await adminFetcher.post("/auth/logout")
}

export const meAsAdmin = async (): Promise<LoginOutput> => {
	const { data } = await adminFetcher.get("/me")
	return data
}
