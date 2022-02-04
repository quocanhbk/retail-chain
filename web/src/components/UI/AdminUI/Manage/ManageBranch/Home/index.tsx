import { getBranches } from "@api"
import { Box, Button, Flex, Heading, SimpleGrid, Text, Wrap, WrapItem } from "@chakra-ui/react"
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
	const { data, isLoading, isError } = useQuery(["branches", throttledSearchText, currentSort.key, currentSort.order], () =>
		getBranches({ search: throttledSearchText, sort_key: currentSort.key, sort_order: currentSort.order })
	)
	const render = () => {
		if (isLoading) {
			return (
				<SimpleGrid spacing={4} columns={[1, 2, 4]}>
					{[...Array(12)].map((_, index) => (
						<BranchCardSkeleton key={index} />
					))}
				</SimpleGrid>
			)
		}

		if (!data || isError) {
			return <Text>{"Đã xảy ra lỗi! Vui lòng thử lại sau."}</Text>
		}

		if (data && data.length === 0) {
			return <Text>{"Chưa có chi nhánh nào."}</Text>
		}

		if (data) {
			return (
				<SimpleGrid spacing={4} columns={[1, 2, 4]}>
					{data.map((branch, index) => (
						<BranchCard data={branch} key={branch.id} index={index} />
					))}
				</SimpleGrid>
			)
		}
	}

	return (
		<Box p={4}>
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
