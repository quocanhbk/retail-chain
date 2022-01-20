import { employeeRoles } from "@constants"

export interface DateInput {
	day: number | null
	month: number | null
	year: number | null
}

export type Role = typeof employeeRoles[number]
