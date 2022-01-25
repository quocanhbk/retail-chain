import AdminLayout from "@components/module/Layout/AdminLayout"
import HomeItemUI from "@components/UI/AdminUI/Manage/ManageItem/Home"
import { ReactElement } from "react"

const EmployeeHomePage = () => {
	return <HomeItemUI />
}

EmployeeHomePage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default EmployeeHomePage
