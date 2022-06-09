import fetcher from "./fetcher"

export interface Supplier {
	id: number
	store_id: number
	code: string
	name: string
	address: string
	email: string
	phone: string | null
	tax: string
	note: string
}

export type CreateSupplierInput = Omit<Supplier, "id" | "store_id">

export const createSupplier = async (input: CreateSupplierInput): Promise<Supplier> => {
	const { data } = await fetcher.post("/supplier", { ...input, code: input.code || null, email: input.email || null })
	return data
}

export const getSuppliers = async (search?: string): Promise<Supplier[]> => {
	const { data } = await fetcher.get(`/supplier${search ? `?search=${search}` : ""}`)
	return data
}
export const getSupplier = async (id: number): Promise<Supplier> => {
	const { data } = await fetcher.get(`/supplier/${id}`)
	return data
}
export const editSupplier = async (supplierId: number, input: CreateSupplierInput): Promise<Supplier> => {
	const { data } = await fetcher.patch(`/supplier/${supplierId}`, input)
	return data
}

export const deleteSupplier = async (supplierId: number): Promise<void> => {
	return await fetcher.delete(`/supplier/${supplierId}`)
}
