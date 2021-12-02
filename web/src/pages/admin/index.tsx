import AdminUI from "@components/UI/AdminUI"
import { AdminLayout } from "@components/UI/Layout"
import { ReactElement } from "react"

const Admin = () => {
	return <AdminUI />
}

Admin.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default Admin
