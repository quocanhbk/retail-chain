import { useQuery } from "react-query"
import { useState } from "react"
import { useThrottle } from "@hooks"
import { returnPurchaseSheetSorts } from "@constants"
import { getReturnPurchaseSheets } from "@api"

const useImportHome = () => {
	const [search, setSearch] = useState("")
	const throttledSearch = useThrottle(search, 500)
	const [currentSort, setCurrentSort] = useState(returnPurchaseSheetSorts[0])

	const returnPurchaseSheetsQuery = useQuery(["return-purchase-sheets", throttledSearch, currentSort.key, currentSort.order], () =>
		getReturnPurchaseSheets({ search: throttledSearch, sort_by: currentSort.key, sort_type: currentSort.order })
	)

	return {
		returnPurchaseSheetsQuery,
		search,
		setSearch,
		currentSort,
		setCurrentSort,
		throttledSearch
	}
}

export default useImportHome
