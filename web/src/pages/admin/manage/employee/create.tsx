import AdminLayout from "@components/UI/AdminUI/AdminLayout"
import CreateEmployeeUI from "@components/UI/AdminUI/Manage/ManageEmployee/Create"
import { ReactElement } from "react"

const CreateEmployeePage = () => {
	return <CreateEmployeeUI />
}

CreateEmployeePage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default CreateEmployeePage
