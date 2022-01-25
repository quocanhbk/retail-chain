import { Box, Button, Flex, Heading, Text, Wrap, WrapItem } from "@chakra-ui/react"
import Link from "next/link"
import useImportHome from "./useImportHome"
import PurchaseSheetCard from "./PurchaseSheetCard"
import PurchaseSheetCardSkeleton from "./PurchaseSheetCardSkeleton"
import { SearchInput } from "@components/shared"

const ImportHomeUI = () => {
	const { purchaseSheetsQuery, search, setSearch } = useImportHome()
	const { data, isLoading, isError } = purchaseSheetsQuery

	const render = () => {
		if (isLoading) {
			return (
				<Wrap spacing={4}>
					{[...Array(10)].map((_, i) => (
						<WrapItem key={i}>
							<PurchaseSheetCardSkeleton />
						</WrapItem>
					))}
				</Wrap>
			)
		}

		if (!data || isError) {
			return <Text>{"Có lỗi xảy ra, vui lòng thử lại sau"}</Text>
		}

		return (
			<Wrap spacing={4}>
				{data.map(ps => (
					<WrapItem key={ps.id}>
						<PurchaseSheetCard data={ps} />
					</WrapItem>
				))}
			</Wrap>
		)
	}

	return (
		<Box p={4}>
			<Flex w="full" align="center" justify="space-between" mb={4}>
				<Heading fontSize={"2xl"}>Nhập hàng</Heading>
				<Link href="/main/inventory/import/create">
					<Button size="sm" variant="ghost">
						{"Nhập hàng"}
					</Button>
				</Link>
			</Flex>
			<SearchInput value={search} onChange={e => setSearch(e.target.value)} mb={4} />
			<Box>{render()}</Box>
		</Box>
	)
}

export default ImportHomeUI
