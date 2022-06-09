import { getItemsBySearch } from "@api"
import { useThrottle } from "@hooks"
import { useState } from "react"
import { useQuery } from "react-query"

const useSearchQuery = () => {
	const [searchText, setSearchText] = useState("")

	const throttledText = useThrottle(searchText, 1000)

	const searchQuery = useQuery(["getItemsBySearch", throttledText], () => getItemsBySearch(throttledText), {
		enabled: throttledText.length > 0
	})

	return {
		searchText,
		setSearchText,
		searchQuery
	}
}

export default useSearchQuery
