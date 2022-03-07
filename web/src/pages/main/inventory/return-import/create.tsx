import { EmployeeLayout } from "@components/module"
import ReturnImportCreateUI from "@components/UI/InventoryUI/ReturnImportUI/Create"
import { ReactElement } from "react"

const ImportCreatePage = () => {
	return <ReturnImportCreateUI />
}

ImportCreatePage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout maxW="80rem">{page}</EmployeeLayout>
}

export default ImportCreatePage
