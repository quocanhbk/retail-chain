import AdminLayout from "@components/UI/AdminUI/AdminLayout"
import EmployeeAdminUI from "@components/UI/AdminUI/Employee"
import { ReactElement } from "react"

const EmployeeAdmin = () => {
	return <EmployeeAdminUI />
}

EmployeeAdmin.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default EmployeeAdmin
