import { useQuery } from "react-query"
import { getPurchaseSheets } from "@api"
import { useState } from "react"
import { useThrottle } from "@hooks"
import { purchaseSheetSorts } from "@constants"

const useImportHome = () => {
	const [search, setSearch] = useState("")
	const throttledSearch = useThrottle(search, 500)
	const [currentSort, setCurrentSort] = useState(purchaseSheetSorts[0])

	const purchaseSheetsQuery = useQuery(["purchase-sheets", throttledSearch, currentSort.key, currentSort.order], () =>
		getPurchaseSheets({ search: throttledSearch, sort_by: currentSort.key, sort_type: currentSort.order })
	)

	return {
		purchaseSheetsQuery,
		search,
		setSearch,
		currentSort,
		setCurrentSort,
		throttledSearch
	}
}

export default useImportHome
