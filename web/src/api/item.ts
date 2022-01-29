import fetcher from "./fetcher"

export interface Item {
	id: number
	code: string
	name: string
	barcode: string
	image: string
	category: Category | null
}

export interface Category {
	id: number
	name: string
}
export type CategoryInput = Omit<Category, "id">

export interface CreateProductInput extends Omit<Item, "id" | "category"> {
	category_id: number
	base_price: number
	sell_price: number
	quantily: number
}

export const createItem = async (input: CreateProductInput): Promise<Item> => {
	const { data } = await fetcher.post("/item", input)
	return data
}
export const getItemsBySearch = async (search: string): Promise<{ currentItems: Item[]; defaultItems: Item[] }> => {
	const { data } = await fetcher.get(`/item/search?search=${search}`)
	return {
		currentItems: data.current,
		defaultItems: data.default
	}
}

export const getItems = async (): Promise<Item[]> => {
	const { data } = await fetcher.get(`/item`)
	return data
}

export const createCategory = async (input: CategoryInput): Promise<Category> => {
	const { data } = await fetcher.post("/item-category", input)
	return data
}
export const getCategories = async (search?: string): Promise<Category[]> => {
	const { data } = await fetcher.get(`/item-category${search ? `?search=${search}` : ""}`)
	return data
}

export const moveItem = async (barcode: string) => {
	const { data } = await fetcher.post(`/item/move`, { barcode })
	return data
}

export const getLastPurchasePrice = async (itemId: number): Promise<number> => {
	const { data } = await fetcher.get(`/item/last-purchase-price/${itemId}`)
	return data
}
