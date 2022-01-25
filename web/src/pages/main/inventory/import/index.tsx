import { EmployeeLayout } from "@components/module"
import ImportHomeUI from "@components/UI/InventoryUI/ImportUI/Home"
import { ReactElement } from "react"

const ImportHomePage = () => {
	return <ImportHomeUI />
}

ImportHomePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default ImportHomePage
