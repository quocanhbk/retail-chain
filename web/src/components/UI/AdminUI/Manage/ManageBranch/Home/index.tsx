import { getBranches } from "@api"
import { Box, Button, Flex, Heading, Wrap, WrapItem } from "@chakra-ui/react"
import { useQuery } from "react-query"
import BranchCard from "./BranchCard/BranchCard"
import BranchCardSkeleton from "./BranchCard/BranchCardSkeleton"
import Link from "next/link"
import { useState } from "react"
import { useThrottle } from "@hooks"
import { SearchInput } from "@components/shared"
import Sorter from "./Sorter"

const HomeBranchUI = () => {
	const [searchText, setSearchText] = useState("")
	const [currentSort, setCurrentSort] = useState({ key: "name", order: "asc" })
	const throttledSearchText = useThrottle(searchText, 500)
	const { data, isLoading } = useQuery(["branches", throttledSearchText, currentSort.key, currentSort.order], () =>
		getBranches({ search: throttledSearchText, sort_key: currentSort.key, sort_order: currentSort.order })
	)
	const render = () => {
		if (isLoading) {
			return (
				<Wrap spacing={4}>
					{[...Array(12)].map((_, index) => (
						<WrapItem key={index}>
							<BranchCardSkeleton />
						</WrapItem>
					))}
				</Wrap>
			)
		}
		if (data) {
			return (
				<Wrap spacing={4}>
					{data.map((branch, index) => (
						<WrapItem key={branch.id}>
							<BranchCard data={branch} index={index} />
						</WrapItem>
					))}
				</Wrap>
			)
		}
	}

	return (
		<Box p={2}>
			<Flex w="full" align="center" justify="space-between" mb={4}>
				<Heading fontSize={"2xl"}>Quản lý chi nhánh</Heading>
				<Link href="/admin/manage/branch/create">
					<Button size="sm" variant="ghost">
						{"Tạo chi nhánh"}
					</Button>
				</Link>
			</Flex>
			<Flex align="center" mb={4}>
				<SearchInput
					value={searchText}
					onChange={e => setSearchText(e.target.value)}
					placeholder="Tìm kiếm chi nhánh"
					onClear={() => setSearchText("")}
				/>
				<Sorter currentSort={currentSort} onChange={setCurrentSort} />
			</Flex>
			{render()}
		</Box>
	)
}

export default HomeBranchUI
