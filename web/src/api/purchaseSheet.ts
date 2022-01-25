import { Item } from "./item"

export interface PurchaseSheetItem {
	id: number
	purchase_sheet_id: number
	item_id: number
	quantity: number
	price: number
	discount: number
	discount_type: string
}

export interface PurchaseSheet {
	id: number
	code: string
	employee_id: number
	branch_id: number
	supplier_id: number | null
	discount: number
	discount_type: string
	note?: string
}

export interface CreatePurchaseSheetInput extends Pick<PurchaseSheet, "code" | "supplier_id" | "discount" | "discount_type" | "note"> {
	items: (Pick<PurchaseSheetItem, "quantity" | "price" | "discount" | "discount_type" | "item_id"> & { item: Item })[]
}
