import { EmployeeLayout } from "@components/module"
import ImportHomeUI from "@components/UI/InventoryUI/ImportUI/Home"
import { ReactElement } from "react"

const CartPage = () => {
	return <ImportHomeUI />
}

CartPage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default CartPage
