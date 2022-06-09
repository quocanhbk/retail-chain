import { EmployeeLayout } from "@components/module"
import ReturnImportHomeUI from "@components/UI/InventoryUI/ReturnImportUI/Home"
import { ReactElement } from "react"

const ImportHomePage = () => {
	return <ReturnImportHomeUI />
}

ImportHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default ImportHomePage
