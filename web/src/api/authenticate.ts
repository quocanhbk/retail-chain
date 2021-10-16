import fetcher from "./fetcher"

export interface LoginInput extends Record<"username" | "password", string> {}

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
	state: "success" | "fail"
	errors: Partial<Record<keyof RegisterInput, string[]>> | "none"
}

export interface UserInfo {
	userId: number
	name: string
	username: string
	avatar: string
	email: string
	phone: string
	gender: string
	dateOfBirth: string
	storeId: number
	storeName: string
	storeOwnerId: number
	branchId: number
	branchName: string
	branchAddress: string
	roles: string[]
}

export const register = async (input: RegisterInput): Promise<RegisterOutput> => {
	const { data } = await fetcher.post("/register", input)
	return {
		state: data.state,
		errors: data.errors,
	}
}

export const login = async (input: LoginInput): Promise<UserInfo> => {
	const {
		data: { user_info },
	} = await fetcher.post("/login", input)
	return user_info
}
