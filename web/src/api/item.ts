import fetcher from "./fetcher"

export interface Item {
	id: number
	name: string
	barcode: string
	image: string
	category: Category
}

export interface Category {
	id: number
	name: string
}

export const getItemsBySearch = async (search: string): Promise<{ currentItems: Item[]; defaultItems: Item[] }> => {
	const { data } = await fetcher.get(`/item/search?search=${search}`)
	return {
		currentItems: data.current,
		defaultItems: data.default
	}
}

export const moveItem = async (barcode: string) => {
	const { data } = await fetcher.post(`/item/move`, { barcode })
	return data
}
