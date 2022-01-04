import AdminLayout from "@components/UI/AdminUI/AdminLayout"
import StoreDashboardUI from "@components/UI/AdminUI/Dashboard"
import { ReactElement } from "react"

const StoreDashboard = () => {
	return <StoreDashboardUI />
}

StoreDashboard.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default StoreDashboard
