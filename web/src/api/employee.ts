import { DateInput } from "@components/module/DateInput.tsx"
import fetcher from "./fetcher"

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
	birthday: DateInput | null
	avatar: File | string | null
	gender: string | null
	employment: Employment
}

export interface CreateEmployeeInput extends Omit<Employee, "id" | "store_id" | "employment"> {
	password: string
	password_confirmation: string
	branch_id: number
	roles: string[]
}

export const createEmployee = async (input: CreateEmployeeInput): Promise<Employee> => {
	// append input to formData
	const formData = new FormData()
	Object.entries(input)
		.filter(([key]) => key !== "birthday" && key !== "avatar" && key !== "roles")
		.forEach(([key, value]) => {
			formData.append(key, value)
		})
	input.roles.forEach(role => {
		formData.append("roles[]", role)
	})

	if (input.birthday) {
		formData.append(
			"birthday",
			`${input.birthday.year}-${input.birthday.month?.toLocaleString(undefined, {
				minimumIntegerDigits: 2,
			})}-${input.birthday.day?.toLocaleString(undefined, { minimumIntegerDigits: 2 })}`
		)
	}
	if (input.avatar instanceof File) {
		formData.append("avatar", input.avatar)
	}

	const { data } = await fetcher.post("/employee", formData)
	return data
}

export const getEmployees = async (): Promise<Employee[]> => {
	const { data } = await fetcher.get(`/employee`)
	return data
}

export const getEmployeesByBranchId = async (branchId: number): Promise<Employee[]> => {
	const { data } = await fetcher.get(`/employee/branch/${branchId}`)
	return data
}
