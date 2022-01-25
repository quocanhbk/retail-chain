import EmployeeLayout from "@components/UI/EmployeeUI/EmployeeLayout"

import { ReactElement } from "react"

const SupplierHomePage = () => {
	return <div>SupplierHomePage</div>
}

SupplierHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default SupplierHomePage
