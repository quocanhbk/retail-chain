import fetcher from "../fetcher"
import { LoginInput } from "./employee"

export interface RegisterStoreInput {
	name: string
	email: string
	password: string
	password_confirmation: string
	remember: boolean
}

export type RegisterStoreOutput = Pick<RegisterStoreInput, "name" | "email">

export const registerStore = async (input: RegisterStoreInput): Promise<RegisterStoreOutput> => {
	const { data } = await fetcher.post("/store/register", input)
	return data
}

export const loginStore = async (input: LoginInput): Promise<RegisterStoreOutput> => {
	const { data } = await fetcher.post("/store/login", input)
	return data
}

export const logoutStore = async (): Promise<void> => {
	await fetcher.post("/store/logout")
}

export const getStoreInfo = async (): Promise<RegisterStoreOutput> => {
	const { data } = await fetcher.get("/store/me")
	return data
}

export const getGuard = async (): Promise<string> => {
	const { data } = await fetcher.get("/auth")
	return data
}
