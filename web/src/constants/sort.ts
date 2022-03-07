export interface SortField {
	key: string
	order: "asc" | "desc"
	text: string
}

export type SortSelections = SortField[]

export const branchSorts: SortSelections = [
	{ key: "created_at", order: "asc", text: "Ngày tạo tăng dần" },
	{ key: "created_at", order: "desc", text: "Ngày tạo giảm dần" },
	{ key: "name", order: "asc", text: "Tên chi nhánh theo A - Z" },
	{ key: "name", order: "desc", text: "Tên chi nhánh theo Z - A" }
]

export const purchaseSheetSorts: SortSelections = [
	{ key: "created_at", order: "asc", text: "Ngày tạo tăng dần" },
	{ key: "created_at", order: "desc", text: "Ngày tạo giảm dần" },
	{ key: "code", order: "asc", text: "Mã phiếu nhập theo A - Z" },
	{ key: "code", order: "desc", text: "Mã phiếu nhập theo Z - A" }
]

export const returnPurchaseSheetSorts: SortSelections = [
	{ key: "created_at", order: "asc", text: "Ngày tạo tăng dần" },
	{ key: "created_at", order: "desc", text: "Ngày tạo giảm dần" },
	{ key: "code", order: "asc", text: "Mã phiếu nhập theo A - Z" },
	{ key: "code", order: "desc", text: "Mã phiếu nhập theo Z - A" }
]
