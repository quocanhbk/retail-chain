import { AuthState, Info } from "@@types"
import fetcher from "./fetcher"

export interface LoginInput extends Record<"email" | "password", string> {}
export interface RegisterInput extends LoginInput {
	name: string
	email: string
	password: string
	store_name: string
}

export interface RegisterOutput {
	state: AuthState
	errors: Partial<Record<keyof RegisterInput, string[]>> | "none"
	info: Info
}

export const register = async (input: RegisterInput): Promise<RegisterOutput> => {
	const { data } = await fetcher.post("/register", input)
	return data
}

export interface LoginOutput {
	state: AuthState
	errors: string
	info: Info
}

export const login = async (input: LoginInput): Promise<LoginOutput> => {
	const { data } = await fetcher.post("/login", input)
	return data
}

export const loginAdmin = async (input: LoginInput): Promise<LoginOutput> => {
	const { data } = await fetcher.post("/login/admin", input)
	return data
}

export const logout = async (): Promise<void> => {
	await fetcher.post("/logout")
}

export const me = async (): Promise<LoginOutput> => {
	const { data } = await fetcher.get("/me")
	return data
}
export const meAdmin = async (): Promise<LoginOutput> => {
	const { data } = await fetcher.get("/me/admin")
	return data
}
