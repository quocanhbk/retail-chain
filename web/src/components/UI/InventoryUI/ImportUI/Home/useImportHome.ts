import { useQuery } from "react-query"
import { getPurchaseSheets } from "@api"
import { useThrottle } from "@hooks"
import { useState } from "react"

const useImportHome = () => {
	const [search, setSearch] = useState("")

	const purchaseSheetsQuery = useQuery(["purchase-sheets"], getPurchaseSheets)

	return {
		purchaseSheetsQuery,
		search,
		setSearch
	}
}

export default useImportHome
