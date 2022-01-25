import { EmployeeLayout } from "@components/module"
import ImportCreateUI from "@components/UI/InventoryUI/ImportUI/Create"
import { ReactElement } from "react"

const ImportCreatePage = () => {
	return <ImportCreateUI />
}

ImportCreatePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default ImportCreatePage
