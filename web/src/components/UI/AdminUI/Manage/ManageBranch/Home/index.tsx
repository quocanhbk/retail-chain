import { getBranches } from "@api"
import {
	Box,
	Button,
	Flex,
	Heading,
	IconButton,
	Input,
	InputGroup,
	InputLeftElement,
	InputRightElement,
	Wrap,
	WrapItem,
} from "@chakra-ui/react"
import { useQuery } from "react-query"
import BranchCard from "./BranchCard/BranchCard"
import BranchCardSkeleton from "./BranchCard/BranchCardSkeleton"
import Link from "next/link"
import { useState } from "react"
import { useThrottle } from "@hooks"
import { SearchInput } from "@components/shared"

const HomeBranchUI = () => {
	const [searchText, setSearchText] = useState("")
	const throttledSearchText = useThrottle(searchText, 500)
	const { data, isLoading } = useQuery(["branches", throttledSearchText], () => getBranches(throttledSearchText))
	const render = () => {
		if (isLoading) {
			return (
				<Wrap>
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
				<Wrap>
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
		<Box p={4}>
			<Flex w="full" align="center" justify="space-between">
				<Heading mb={4} fontSize={"2xl"}>
					Quản lý chi nhánh
				</Heading>
				<Link href="/admin/manage/branch/create">
					<Button size="sm" variant="ghost">
						{"Tạo chi nhánh"}
					</Button>
				</Link>
			</Flex>
			<SearchInput
				value={searchText}
				onChange={e => setSearchText(e.target.value)}
				placeholder="Tìm kiếm chi nhánh"
				mb={4}
				onClear={() => setSearchText("")}
			/>

			{render()}
		</Box>
	)
}

export default HomeBranchUI