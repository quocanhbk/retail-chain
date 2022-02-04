import AdminLayout from "@components/module/Layout/AdminLayout"
import CreateSupplierUI from "@components/UI/AdminUI/Manage/ManageSupplier/Create"
import { useRouter } from "next/router"
import { ReactElement } from "react"

const SupplierDetailPage = () => {
	const router = useRouter()
	return <CreateSupplierUI id={parseInt(router.query.id as string)} />
}

SupplierDetailPage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default SupplierDetailPage
