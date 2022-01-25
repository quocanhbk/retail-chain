import AdminLayout from "@components/module/Layout/AdminLayout"
import BranchDetailUI from "@components/UI/AdminUI/Manage/ManageBranch/Detail"
import { useRouter } from "next/router"
import { ReactElement } from "react"

const BranchDetailPage = () => {
	const router = useRouter()
	return <BranchDetailUI id={parseInt(router.query.id as string)} />
}

BranchDetailPage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default BranchDetailPage
