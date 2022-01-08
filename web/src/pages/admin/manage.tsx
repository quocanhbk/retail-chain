import AdminLayout from "@components/UI/AdminUI/AdminLayout"
import StoreManageUI from "@components/UI/AdminUI/Manage"
import { ReactElement } from "react"

const StoreManage = () => {
	return <StoreManageUI />
}

StoreManage.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default StoreManage
