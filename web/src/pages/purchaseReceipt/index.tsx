import EmployeeLayout from "@components/UI/EmployeeUI/EmployeeLayout"

import { ReactElement } from "react"

const PurchaseReceiptHomePage = () => {
	return <div>PurchaseReceiptHomePage</div>
}

PurchaseReceiptHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default PurchaseReceiptHomePage
