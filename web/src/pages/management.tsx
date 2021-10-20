import { MainLayout } from "@components/UI/Layout"
import ManagementUI from "@components/UI/ManagementUI"
import { ReactElement } from "react"
import { NextPageWithLayout } from "./_app"

const Management: NextPageWithLayout = () => {
	return <ManagementUI />
}

Management.getLayout = function getLayout(page: ReactElement) {
	return <MainLayout>{page}</MainLayout>
}

export default Management
