import AdminLayout from "@components/module/Layout/AdminLayout"
import HomeBranchUI from "@components/UI/AdminUI/Manage/ManageBranch/Home"
import { ReactElement } from "react"

const BranchHomePage = () => {
	return <HomeBranchUI />
}

BranchHomePage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default BranchHomePage
