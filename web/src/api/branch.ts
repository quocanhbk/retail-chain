import fetcher from "./fetcher"

export interface Branch {
	id: number
	name: string
	address: string
	image: string | File | null
}

export type CreateBranchInput = Omit<Branch, "id">

export const getBranches = async (): Promise<Branch[]> => {
	const { data } = await fetcher.get("/branch")
	return data
}

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
