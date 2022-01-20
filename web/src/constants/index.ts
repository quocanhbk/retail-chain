export const adminNavMenus = [
	{ id: "", text: "Trang chủ", path: "/admin", subMenus: [] },
	{
		id: "manage",
		text: "Quản lý",
		path: "/admin/manage/branch",
		subMenus: [
			{ id: "branch", text: "Chi nhánh", path: "/admin/manage/branch" },
			{ id: "employee", text: "Nhân viên", path: "/admin/manage/employee" },
			{ id: "item", text: "Sản phẩm", path: "/admin/manage/item" },
			{ id: "supplier", text: "Nhà cung cấp", path: "/admin/manage/supplier" }
		]
	}
]

export const employeeRoles = [
	{ id: "manage", value: "Quản lý" },
	{ id: "purchase", value: "Nhập hàng" },
	{ id: "sale", value: "Bán hàng" }
] as const

export const genders = [
	{ id: "male", value: "Nam" },
	{ id: "female", value: "Nữ" },
	{ id: "unknown", value: "Không xác định" }
]

export const branchSorts = [
	{ key: "created_at", order: "asc", text: "Ngày tạo tăng dần" },
	{ key: "created_at", order: "desc", text: "Ngày tạo giảm dần" },
	{ key: "name", order: "asc", text: "Tên chi nhánh theo A - Z" },
	{ key: "name", order: "desc", text: "Tên chi nhánh theo Z - A" }
]
