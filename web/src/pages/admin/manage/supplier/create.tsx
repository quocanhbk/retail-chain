import AdminLayout from "@components/module/Layout/AdminLayout"
import CreateSupplierUI from "@components/UI/AdminUI/Manage/ManageSupplier/Create"
import { ReactElement } from "react"

const CreateSupplierPage = () => {
	return <CreateSupplierUI />
}

CreateSupplierPage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default CreateSupplierPage
