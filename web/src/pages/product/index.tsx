import EmployeeLayout from "@components/UI/EmployeeUI/EmployeeLayout"
import InventoryUI from "@components/UI/EmployeeUI/InventoryUI"
import CategoryUI from "@components/UI/EmployeeUI/InventoryUI/CategoryUI"

import { ReactElement } from "react"

const ProductHomePage = () => {
	return <CategoryUI/>
}

ProductHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default ProductHomePage
