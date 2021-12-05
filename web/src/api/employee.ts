import { AuthState, User } from "@@types"
import { adminFetcher } from "./fetcher"

interface CreateEmployeeInput {
	name: string
	email: string
	password: string
	branch_id: number
	roles: string[]
}

export const createEmployee = async (
	input: CreateEmployeeInput
): Promise<{ state: AuthState; errors: Partial<Record<keyof CreateEmployeeInput, string[]>> | undefined }> => {
	const { data } = await adminFetcher.post("/employee", input)
	return data
}

export const getEmployees = async (
	branch_id: number
): Promise<{ state: AuthState; errors: string | undefined; info: { users: User[] } }> => {
	const { data } = await adminFetcher.get(`/employee/${branch_id}`)
	return data
}
