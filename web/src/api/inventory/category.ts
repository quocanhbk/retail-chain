import fetcher from "../fetcher"

export interface Category {
	id: number
	branch_id: number
	name: string
	point_ratio: number
}

export const getCategoryAPI = async (storeId: number, branchId: number, token: string): Promise<Category[]> => {
	const { data } = await fetcher.get(`/store/${storeId}/branch/${branchId}/category/get`, {
		headers: {
			Authorization: `Bearer ${token}`,
		},
	})
	return data.category
}

export const addCategoryAPI = async (
	storeId: number,
	branchId: number,
	token: string,
	name: string
): Promise<boolean> => {
	const { data } = await fetcher.post(
		`/store/${storeId}/branch/${branchId}/category/create`,
		{
			name,
		},
		{
			headers: {
				Authorization: `Bearer ${token}`,
			},
		}
	)
	return data.state === "success"
}

export const updateCategoryAPI = async (
	storeId: number,
	branchId: number,
	token: string,
	categoryId: number,
	name: string,
	ratio: number
) => {
	const { data } = await fetcher.post(
		`/store/${storeId}/branch/${branchId}/category/edit`,
		{
			category_id: categoryId,
			name,
			point_ratio: ratio,
		},
		{
			headers: {
				Authorization: `Bearer ${token}`,
			},
		}
	)
	return data.state === "success"
}

export const deleteCategoryAPI = async (storeId: number, branchId: number, token: string, categoryId: number) => {
	const { data } = await fetcher.post(
		`/store/${storeId}/branch/${branchId}/category/edit`,
		{
			category_id: categoryId,
			deleted: true,
		},
		{
			headers: {
				Authorization: `Bearer ${token}`,
			},
		}
	)
	return data.state === "success"
}
