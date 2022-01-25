import EmployeeLayout from "@components/UI/EmployeeUI/EmployeeLayout"

import { ReactElement } from "react"

const PriceBookHomePage = () => {
	return <div>priceBook</div>
}

PriceBookHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default PriceBookHomePage
