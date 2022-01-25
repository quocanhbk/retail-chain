import AdminLayout from "@components/module/Layout/AdminLayout"
import HomeEmployeeUI from "@components/UI/AdminUI/Manage/ManageEmployee/Home"
import { ReactElement } from "react"

const EmployeeHomePage = () => {
	return <HomeEmployeeUI />
}

EmployeeHomePage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default EmployeeHomePage
