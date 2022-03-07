import { ListQueryOptions } from "@@types"
import { toQueryString } from "@helper"
import { Branch } from "./branch"
import { Employee } from "./employee"
import fetcher from "./fetcher"
import { Item } from "./item"
import { Supplier } from "./supplier"

export interface ReturnPurchaseItem {
	id: number
	return_purchase_sheet_id: number
	item_id: number
	quantity: number
	return_price: number
	return_price_type: "cash" | "percent"
	total: number
}

export interface ReturnPurchaseItemDetail extends ReturnPurchaseItem {
	item: Item
}

export interface ReturnPurchaseSheet {
	id: number
	purchase_sheet_id: number
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

export interface ReturnPurchaseSheetDetail extends ReturnPurchaseSheet {
	employee: Employee
	branch: Branch
	supplier: Supplier
	return_purchase_sheet_items: ReturnPurchaseItemDetail[]
	created_at: string
}

export interface CreateReturnPurchaseSheetInput
	extends Pick<ReturnPurchaseSheet, "code" | "supplier_id" | "discount" | "discount_type" | "note" | "paid_amount" | "purchase_sheet_id"> {
	items: (Pick<ReturnPurchaseItem, "item_id" | "quantity" | "return_price" | "return_price_type"> & { item: Item })[]
}

export const createReturnPurchaseSheet = async (input: CreateReturnPurchaseSheetInput) => {
	const { data } = await fetcher.post("/return-purchase-sheet", input)
	return data
}

export const updateReturnPurchaseSheet = async (id: number, input: CreateReturnPurchaseSheetInput) => {
	const { data } = await fetcher.patch(`/return-purchase-sheet/${id}`, input)
	return data
}

export const getReturnPurchaseSheets = async (
	options: ListQueryOptions
): Promise<Omit<ReturnPurchaseSheetDetail, "employee" | "branch" | "returnPurchaseSheetItems">[]> => {
	const { data } = await fetcher.get(`/return-purchase-sheet?${toQueryString(options)}`)
	return data
}

export const getReturnPurchaseSheet = async (id: number): Promise<ReturnPurchaseSheetDetail> => {
	const { data } = await fetcher.get(`/return-purchase-sheet/${id}`)
	return data
}

export const deleteReturnPurchaseSheet = async (id: number) => {
	const { data } = await fetcher.delete(`/return-purchase-sheet/${id}`)
	return data
}

export const getReturnableItems = async (purchase_sheet_id: number): Promise<(Item & { quantity: number; base_price: number })[]> => {
	const { data } = await fetcher.get(`/return-purchase-sheet/returnable/${purchase_sheet_id}`)
	return data
}
