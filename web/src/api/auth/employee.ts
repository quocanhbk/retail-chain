import fetcher from "../fetcher"

export interface LoginEmployeeInput {
	email: string
	password: string
	remember: boolean
}

export interface LoginEmployeeOutput {
	name: string
	email: string
	avatar_url: string | null
	phone: string | null
	birthday: string | null
	gender: string | null
}

export const loginEmployee = async (input: LoginEmployeeInput): Promise<LoginEmployeeOutput> => {
	const { data } = await fetcher.post("/employee/login", input)
	return data
}

export const logoutEmployee = async (): Promise<void> => {
	await fetcher.post("/employee/logout")
}

export const getInfoEmployee = async (): Promise<LoginEmployeeOutput> => {
	const { data } = await fetcher.get("/employee/me")
	return data
}
