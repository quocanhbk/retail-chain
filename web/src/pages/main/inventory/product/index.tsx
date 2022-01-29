import { EmployeeLayout } from "@components/module"
import ProductHomeUI from "@components/UI/InventoryUI/ProductUI"

import { ReactElement } from "react"

const ProductHomePage = () => {
	return <ProductHomeUI />
}

ProductHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default ProductHomePage
