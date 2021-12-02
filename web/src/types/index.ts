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
	store: Store
	branch: Branch | null
	roles: Role[] | null
}

export type Role = string

export type AuthState = "success" | "fail"
