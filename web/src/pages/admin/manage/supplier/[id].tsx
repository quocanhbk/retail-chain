import AdminLayout from "@components/UI/AdminUI/AdminLayout"
import DetailSupplierUI from "@components/UI/AdminUI/Manage/ManageSupplier/Detail"
import { useRouter } from "next/router"
import { ReactElement } from "react"

const SupplierDetailPage = () => {
	const router = useRouter()
	return <DetailSupplierUI id={parseInt(router.query.id as string)} />
}

SupplierDetailPage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default SupplierDetailPage
