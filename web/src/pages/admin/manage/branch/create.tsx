import AdminLayout from "@components/module/Layout/AdminLayout"
import CreateBranchUI from "@components/UI/AdminUI/Manage/ManageBranch/Create"
import { ReactElement } from "react"

const CreateBranchPage = () => {
	return <CreateBranchUI />
}

CreateBranchPage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default CreateBranchPage
