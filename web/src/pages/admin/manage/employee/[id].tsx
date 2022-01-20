import AdminLayout from "@components/UI/AdminUI/AdminLayout"
import CreateEmployeeUI from "@components/UI/AdminUI/Manage/ManageEmployee/Create"
import { NextPageWithLayout } from "@pages/_app"
import { useRouter } from "next/router"
import { ReactElement } from "react"

const EmployeeDetailPage: NextPageWithLayout = () => {
	const router = useRouter()
	return <CreateEmployeeUI id={parseInt(router.query.id as string)} />
}

EmployeeDetailPage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default EmployeeDetailPage
