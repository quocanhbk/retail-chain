import EmployeeLayout from "@components/UI/EmployeeUI/EmployeeLayout"

import { ReactElement } from "react"

const PurchaseReturnHomePage = () => {
	return <div>PurchaseReturnHomePage</div>
}

PurchaseReturnHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default PurchaseReturnHomePage
