import fetcher from "./fetcher"

type AuthState = "success" | "fail"

export interface RegisterInput extends LoginInput {
	name: string
	email: string
	phone: string
	date_of_birth: string
	gender: string
	store_name: string
	branch_name: string
	branch_address: string
	avatar: File | null
}

export interface RegisterOutput {
	state: AuthState
	errors: Partial<Record<keyof RegisterInput, string[]>> | "none"
}

export interface LoginInput extends Record<"username" | "password", string> {}

export interface UserInfo {
	user_id: number
	name: string
	username: string
	avatar_url: string
	email: string
	phone: string
	gender: string
	date_of_birth: string
	store_id: number
	store_name: string
	store_owner_id: number
	branch_id: number
	branch_name: string
	branch_address: string
	roles: string[]
}

export interface LoginOutput {
	state: AuthState
	errors: string
	user_info: UserInfo
	token: string
}

export const register = async (input: RegisterInput): Promise<RegisterOutput> => {
	const { data } = await fetcher.post("/register", input)
	return {
		state: data.state,
		errors: data.errors,
	}
}

export const login = async (input: LoginInput): Promise<LoginOutput> => {
	const { data } = await fetcher.post("/login", input)
	return data
}

export const logout = async () => {
	const { data } = await fetcher.post("/logout")
	console.log(data)
}
