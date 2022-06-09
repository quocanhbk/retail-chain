/* eslint-disable no-mixed-spaces-and-tabs */
import { DateInput } from "@@types"
import fetcher, { baseURL } from "./fetcher"

export interface EmploymentRole {
	id: number
	employment_id: number
	role: string
}
export interface Employment {
	id: number
	employee_id: number
	branch_id: number
	from: string
	to: string | null
	roles: EmploymentRole[]
}
export interface Employee {
	id: number
	store_id: number
	name: string
	email: string
	phone: string
	birthday: string | null
	avatar: File | string | null
	avatar_key: string
	gender: string | null
	employment: Employment
}

export interface CreateEmployeeInput extends Omit<Employee, "id" | "store_id" | "employment" | "birthday" | "avatar_key"> {
	password: string
	password_confirmation: string
	branch_id: number
	roles: string[]
	birthday: DateInput | null
}

export const createEmployee = async (input: CreateEmployeeInput): Promise<Employee> => {
	const { data } = await fetcher.post("/employee", {
		...input,
		birthday: input.birthday
			? `${input.birthday.year}-${input.birthday.month?.toLocaleString(undefined, {
					minimumIntegerDigits: 2
			  })}-${input.birthday.day?.toLocaleString(undefined, { minimumIntegerDigits: 2 })}`
			: null
	})

	if (input.avatar instanceof File) {
		await updateAvatar(data.id, input.avatar)
	}

	return data
}

export const getEmployees = async (): Promise<Employee[]> => {
	const { data } = await fetcher.get(`/employee`)
	return data
}

export const getEmployee = async (id: number): Promise<Employee> => {
	const { data } = await fetcher.get(`/employee/${id}`)
	return data
}

export const getEmployeesByBranchId = async (branchId: number): Promise<Employee[]> => {
	const { data } = await fetcher.get(`/employee/branch/${branchId}`)
	return data
}

export const getEmployeeAvatar = (avatar_key: string) => `${baseURL}/employee/avatar/${avatar_key}`

export const updateEmployee = async (id: number, input: Omit<CreateEmployeeInput, "password" | "password_confirmation">) => {
	const { data } = await fetcher.post(`/employee/${id}`, {
		...input,
		birthday: input.birthday
			? `${input.birthday.year}-${input.birthday.month?.toLocaleString(undefined, {
					minimumIntegerDigits: 2
			  })}-${input.birthday.day?.toLocaleString(undefined, { minimumIntegerDigits: 2 })}`
			: null
	})
	if (input.avatar instanceof File) {
		await updateAvatar(id, input.avatar)
	}
	return data
}

export const deleteEmployee = async (id: number) => {
	const { data } = await fetcher.delete(`/employee/${id}`)
	return data
}

export const createManyEmployees = async (input: CreateEmployeeInput[]) => {
	const { data } = await fetcher.post(`/employee/many`, { employees: input })
	return data
}

export const updateAvatar = async (id: number, avatar: File) => {
	const formData = new FormData()
	formData.append("avatar", avatar)
	const { data } = await fetcher.post(`/employee/avatar/${id}`, formData)
	return data
}

export const transferEmployee = async (input: { employee_id: number; branch_id: number; roles: string[] }) => {
	const { data } = await fetcher.post(`/employee/transfer`, input)
	return data
}

export const transferManyEmployees = async (input: { branch_id: number; employees: { id: number; roles: string[] }[] }) => {
	const { data } = await fetcher.post(`/employee/transfer/many`, input)
	return data
}
