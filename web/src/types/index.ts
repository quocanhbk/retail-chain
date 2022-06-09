import { employeeRoles } from "@constants"

export interface DateInput {
	day: number | null
	month: number | null
	year: number | null
}

export type Role = typeof employeeRoles[number]

export interface ListQueryOptions {
	sort_by?: string
	sort_type?: "asc" | "desc"
	search?: string
	from?: number
	to?: number
}
