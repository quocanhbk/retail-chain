import EmployeeLayout from "@components/UI/EmployeeUI/EmployeeLayout"

import { ReactElement } from "react"

const InventoryCountHomePage = () => {
	return <div>InventoryCountHomePage</div>
}

InventoryCountHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default InventoryCountHomePage
