import fetcher from "../fetcher"

export interface Category {
	id: number
	branch_id: number
	name: string
	point_ratio: number
}

export const getCategoryAPI = async (storeId: number, branchId: number): Promise<Category[]> => {
	const { data } = await fetcher.get(`/store/${storeId}/branch/${branchId}/category/get`)
	return data.category
}

export const addCategoryAPI = async (storeId: number, branchId: number, name: string): Promise<boolean> => {
	const { data } = await fetcher.post(`/store/${storeId}/branch/${branchId}/category/create`, {
		name,
	})
	return data.state === "success"
}

export const updateCategoryAPI = async (
	storeId: number,
	branchId: number,
	categoryId: number,
	name: string,
	ratio: number
) => {
	const { data } = await fetcher.post(`/store/${storeId}/branch/${branchId}/category/edit`, {
		category_id: categoryId,
		name,
		point_ratio: ratio,
	})
	return data.state === "success"
}

export const deleteCategoryAPI = async (storeId: number, branchId: number, categoryId: number) => {
	const { data } = await fetcher.post(`/store/${storeId}/branch/${branchId}/category/edit`, {
		category_id: categoryId,
		deleted: true,
	})
	return data.state === "success"
}
