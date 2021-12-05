import AdminLayout from "@components/UI/AdminUI/AdminLayout"
import BranchAdminUI from "@components/UI/AdminUI/Branch"
import { ReactElement } from "react"

const BranchAdmin = () => {
	return <BranchAdminUI />
}

BranchAdmin.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default BranchAdmin
