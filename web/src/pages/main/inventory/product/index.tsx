import { EmployeeLayout } from "@components/module"
import ImportHomeUI from "@components/UI/InventoryUI/ImportUI/Home"
import ProductHomeUI from "@components/UI/InventoryUI/ProductUI"

import { ReactElement } from "react"

const ProductHomePage = () => {
	return <ProductHomeUI/>
}

ProductHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default ProductHomePage
