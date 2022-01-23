import EmployeeLayout from "@components/UI/EmployeeUI/EmployeeLayout"
import HomeUI from "@components/UI/HomeUI"
import { ReactElement } from "react"

const StoreDashboard = () => {
	return <HomeUI/>
}

StoreDashboard.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default StoreDashboard
