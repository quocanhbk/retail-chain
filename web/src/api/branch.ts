import { CreateEmployeeInput } from "./employee"
import fetcher, { baseURL } from "./fetcher"

export interface Branch {
	id: number
	name: string
	address: string
	image: string | File | null
	image_key: string
}

export type NewEmployee = CreateEmployeeInput & { type: "create" }
export type TransferEmployee = Pick<CreateEmployeeInput, "name" | "email" | "roles" | "phone" | "branch_id"> & { type: "transfer"; id: number }
export type AddingEmployee = NewEmployee | TransferEmployee

export type CreateBranchInput = Omit<Branch, "id" | "image_key"> & {
	adding_employees: AddingEmployee[]
}

export const getBranches = async (
	query: {
		search: string
		sort_key: string
		sort_order: string
	} = { search: "", sort_key: "created_at", sort_order: "asc" }
): Promise<Branch[]> => {
	const queryParam = Object.entries(query)
		.map(([key, value]) => `${key}=${value}`)
		.join("&")
	const { data } = await fetcher.get(`/branch?${queryParam}`)
	return data
}

export const getBranch = async (id: number): Promise<Branch> => {
	const { data } = await fetcher.get(`/branch/${id}`)
	return data
}

export const getBranchImage = (image_key: string) => `${baseURL}/branch/image/${image_key}`

export const createBranch = async (input: CreateBranchInput): Promise<Branch> => {
	const { name, address, image, adding_employees } = input
	const new_employees = adding_employees.filter(e => e.type === "create")
	const transfer_employees = adding_employees.filter(e => e.type === "transfer")
	const { data } = await fetcher.post("/branch", { name, address, new_employees, transfer_employees })

	const fd = new FormData()
	if (image instanceof File) {
		fd.append("id", data.id.toString())
		fd.append("image", image)
		await fetcher.post(`/branch/image`, fd)
	}

	return data
}

export const updateBranchImage = async (id: number, image: File): Promise<void> => {
	const formData = new FormData()
	formData.append("id", id.toString())
	formData.append("image", image)
	await fetcher.post("/branch/image", formData)
}

export const editBranch = async (branchId: number, input: Partial<CreateBranchInput>): Promise<Branch> => {
	const { data } = await fetcher.post(`/branch/${branchId}`, input)
	return data
}

export const deleteBranch = async (branchId: number): Promise<void> => {
	return await fetcher.delete(`/branch/${branchId}`)
}
