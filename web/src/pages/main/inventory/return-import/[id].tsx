import { EmployeeLayout } from "@components/module"
import ReturnImportCreateUI from "@components/UI/InventoryUI/ReturnImportUI/Create"
import { useRouter } from "next/router"
import { ReactElement } from "react"

const ImportDetailPage = () => {
	const router = useRouter()
	const id = parseInt(router.query.id as string) || undefined
	return <ReturnImportCreateUI id={id} />
}

ImportDetailPage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout maxW="80rem">{page}</EmployeeLayout>
}

export default ImportDetailPage
