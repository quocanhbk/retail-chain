import { SearchInput } from "@components/shared"
import { useState } from "react"

const Sorter = () => {
    const [searchText, setSearchText] = useState("")
    return (
        <SearchInput 
            value={searchText}
            onChange={e => setSearchText(e.target.value)}
            placeholder="Tìm kiếm hàng hóa"
            mb={2}
            onClear={() => setSearchText("")}
        />
    )
}

export default Sorter