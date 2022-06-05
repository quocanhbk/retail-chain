export const adminNavMenus = [
	{ id: "", text: "Trang chủ", path: "/admin", subMenus: [] },
	{
		id: "manage",
		text: "Quản lý",
		path: "/admin/manage/branch",
		subMenus: [
			{ id: "branch", text: "Chi nhánh", path: "/admin/manage/branch" },
			{ id: "employee", text: "Nhân viên", path: "/admin/manage/employee" },
			{ id: "supplier", text: "Nhà cung cấp", path: "/admin/manage/supplier" }
		]
	}
]

export const employeeNavMenus = [
	{
		id: "sale",
		text: "Bán hàng",
		path: "/main/sale/cart",
		subMenus: [{ id: "cart", text: "Giỏ hàng", path: "/main/sale/cart" }]
	},
	{
		id: "inventory",
		text: "Kho hàng",
		path: "/main/inventory/import",
		subMenus: [
			{ id: "product", text: "Hàng hóa", path: "/main/inventory/product" },
			{ id: "import", text: "Nhập hàng", path: "/main/inventory/import" },
			{ id: "return-import", text: "Trả hàng", path: "/main/inventory/return-import" }
		]
	}
]
