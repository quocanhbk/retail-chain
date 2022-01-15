import fetcher, { baseURL } from "./fetcher"

export interface Branch {
	id: number
	name: string
	address: string
	image: string | File | null
}

export type CreateBranchInput = Omit<Branch, "id">

export const getBranches = async (search = ""): Promise<Branch[]> => {
	const { data } = await fetcher.get(`/branch?search=${search}`)
	return data
}

export const getBranch = async (id: number): Promise<Branch> => {
	const { data } = await fetcher.get(`/branch/${id}`)
	return data
}

export const getBranchImage = (id: number) => `${baseURL}/branch/${id}/image`

export const createBranch = async (input: CreateBranchInput): Promise<Branch> => {
	const formData = new FormData()
	formData.append("name", input.name)
	formData.append("address", input.address)
	if (input.image instanceof File) {
		formData.append("image", input.image)
	}

	const { data } = await fetcher.post("/branch", formData)
	return data
}

export const editBranch = async (branchId: number, input: CreateBranchInput): Promise<Branch> => {
	const formData = new FormData()
	formData.append("name", input.name)
	formData.append("address", input.address)
	if (input.image instanceof File) {
		formData.append("image", input.image)
	}

	const { data } = await fetcher.post(`/branch/${branchId}`, formData)
	return data
}

export const deleteBranch = async (branchId: number): Promise<void> => {
	return await fetcher.delete(`/branch/${branchId}`)
}
