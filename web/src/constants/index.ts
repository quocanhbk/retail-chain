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

export const baseNavMenus = [
	{
		id: "inventory",
		text: "Hàng hóa",
		path: "/product",
		subMenus: [
			{id:"category", text: "Danh mục", path: "/product", enable : true},
			{id:"priceBook", text: "Thiết lập giá", path: "/priceBook", enable: true},
			{id:"inventoryCount", text: "Kiểm kê", path: "/inventoryCount", enable: true}
		]
	},
	{
		id: "transaction",
		text: "Giao dịch",
		path: "/cart",
		subMenus: [
			{id:"cart", text: "Giỏ hàng", path: "/cart", enable : true},
			{id:"invoice", text: "Đơn mua hàng", path: "/invoice", enable: true},
			{id:"return", text: "Trả hàng", path: "/return", enable: true},
			{id:"purchaseReceipt", text: "Nhập hàng", path: "/purchaseReceipt", enable: true},
			{id:"purchaseReturn", text: "Trả hàng nhập", path: "/purchaseReturn", enable: true},
		]
	},
	{
		id: "partner",
		text: "Đối tác",
		path: "/customer",
		subMenus: [
			{id:"customer", text: "Khách hàng", path: "/customer", enable : true},
			{id:"supplier", text: "Nhà cung cấp", path: "/supplier", enable: true},
		]
	},
	
]
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
