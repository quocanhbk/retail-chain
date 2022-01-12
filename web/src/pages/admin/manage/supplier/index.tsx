import AdminLayout from "@components/UI/AdminUI/AdminLayout"
import HomeSupplierUI from "@components/UI/AdminUI/Manage/ManageSupplier/Home"
import { ReactElement } from "react"

const EmployeeHomePage = () => {
	return <HomeSupplierUI />
}

EmployeeHomePage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default EmployeeHomePage
