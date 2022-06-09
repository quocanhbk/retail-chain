import AdminLayout from "@components/module/Layout/AdminLayout"
import CreateEmployeeUI from "@components/UI/AdminUI/Manage/ManageEmployee/Create"
import { ReactElement } from "react"

const CreateEmployeePage = () => {
	return <CreateEmployeeUI />
}

CreateEmployeePage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default CreateEmployeePage
