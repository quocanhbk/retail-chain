export interface Store {
	id: number
	name: string
}

export interface Branch {
	id: number
	name: string
	address: string
	store_id: number
	created_date: string
	deleted: boolean
}

export interface User {
	id: number
	name: string
	avatar_url: string | null
	email: string
	phone: string | null
	birthday: string | null
	gender: "male" | "female" | null
	status: "enabled" | "disabled"
	store_id: number
	is_owner: boolean
}

export interface Info {
	user: User
	store: Store | undefined
	branch: Branch | null | undefined
	roles: Role[] | null | undefined
}

export type Role = string

export type AuthState = "success" | "fail"

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
export interface RegisterOutputAdmin {
	state: AuthState
	errors: Partial<Record<keyof RegisterInput, string[]>> | "none"
	info: Info
}

export interface LoginOutput {
	state: AuthState
	errors: string
	info: Info
}
