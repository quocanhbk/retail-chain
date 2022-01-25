import EmployeeLayout from "@components/UI/EmployeeUI/EmployeeLayout"

import { ReactElement } from "react"

const CustomerHomePage = () => {
	return <div>CustomerHomePage</div>
}

CustomerHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default CustomerHomePage
