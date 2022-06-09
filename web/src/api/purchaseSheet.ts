import { ListQueryOptions } from "@@types"
import { toQueryString } from "@helper"
import { Branch } from "./branch"
import { Employee } from "./employee"
import fetcher from "./fetcher"
import { Item } from "./item"
import { Supplier } from "./supplier"

export interface PurchaseSheetItem {
	id: number
	purchase_sheet_id: number
	item_id: number
	quantity: number
	price: number
	discount: number
	discount_type: "cash" | "percent"
}

export interface PurchaseSheet {
	id: number
	code: string
	employee_id: number
	branch_id: number
	supplier_id: number | null
	discount: number
	discount_type: "cash" | "percent"
	paid_amount: number
	total: number
	note?: string
}

export interface PurchaseSheetItemDetail extends PurchaseSheetItem {
	item: Item
}

export interface PurchaseSheetDetail extends PurchaseSheet {
	purchase_sheet_items: PurchaseSheetItemDetail[]
	employee: Employee
	branch: Branch
	supplier: Supplier
	created_at: string
}

export interface CreatePurchaseSheetInput
	extends Pick<PurchaseSheet, "code" | "supplier_id" | "discount" | "discount_type" | "note" | "paid_amount"> {
	items: (Pick<PurchaseSheetItem, "quantity" | "price" | "discount" | "discount_type" | "item_id"> & { item: Item })[]
}

export const createPurchaseSheet = async (input: CreatePurchaseSheetInput) => {
	const { data } = await fetcher.post("/purchase-sheet", input)
	return data
}

export const updatePurchaseSheet = async (id: number, input: CreatePurchaseSheetInput) => {
	const { data } = await fetcher.patch(`/purchase-sheet/${id}`, input)
	return data
}

export const getPurchaseSheets = async (
	options: ListQueryOptions = {}
): Promise<Omit<PurchaseSheetDetail, "purchaseSheetItems" | "branch">[]> => {
	const { data } = await fetcher.get(`/purchase-sheet?${toQueryString(options)}`)
	return data
}

export const getPurchaseSheet = async (id: number): Promise<PurchaseSheetDetail> => {
	const { data } = await fetcher.get(`/purchase-sheet/${id}`)
	return data
}

export const deletePurchaseSheet = async (id: number) => {
	await fetcher.delete(`/purchase-sheet/${id}`)
}
