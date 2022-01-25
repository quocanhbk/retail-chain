import EmployeeLayout from "@components/UI/EmployeeUI/EmployeeLayout"

import { ReactElement } from "react"

const InvoiceHomePage = () => {
	return <div>InvoiceHomePage</div>
}

InvoiceHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default InvoiceHomePage
