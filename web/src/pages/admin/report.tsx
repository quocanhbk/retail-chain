import AdminLayout from "@components/UI/AdminUI/AdminLayout"
import ReportAdminUI from "@components/UI/AdminUI/Report"
import { ReactElement } from "react"

const ReportAdmin = () => {
	return <ReportAdminUI />
}

ReportAdmin.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default ReportAdmin
