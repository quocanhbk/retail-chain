import { Box, Button, chakra, Flex, Heading, Stack, Text } from "@chakra-ui/react"
import Link from "next/link"
import useReturnImportHome from "./useReturnImportHome"
import ReturnPurchaseSheetCard from "./ReturnPurchaseSheetCard"
import PurchaseSheetCardSkeleton from "./ReturnPurchaseSheetCardSkeleton"
import { SearchInput, Sorter, Table } from "@components/shared"
import { purchaseSheetSorts } from "@constants"

const ReturnImportHomeUI = () => {
	const { returnPurchaseSheetsQuery, search, setSearch, currentSort, setCurrentSort } = useReturnImportHome()
	const { data, isLoading, isError } = returnPurchaseSheetsQuery

	const render = () => {
		if (isLoading) {
			return [...Array(8)].map((_, i) => <PurchaseSheetCardSkeleton key={i} />)
		}

		if (!data || isError) {
			return (
				<chakra.tr>
					<chakra.td colSpan={5} textAlign={"center"}>
						<Text py={4} color={"text.secondary"}>
							{"Có lỗi xảy ra, vui lòng thử lại sau"}
						</Text>
					</chakra.td>
				</chakra.tr>
			)
		}

		return data.map(ps => <ReturnPurchaseSheetCard key={ps.id} data={ps} />)
	}

	return (
		<Flex direction="column" p={4} h="full">
			<Flex w="full" align="center" justify="space-between" mb={4}>
				<Heading fontSize={"2xl"}>Trả hàng nhập</Heading>
			</Flex>
			<Stack direction="row" spacing={4}>
				<SearchInput value={search} onChange={e => setSearch(e.target.value)} mb={4} placeholder="Tìm kiếm phiếu nhập hàng" />
				<Sorter currentSort={currentSort} onChange={setCurrentSort} data={purchaseSheetSorts} />
			</Stack>
			<Table
				header={
					<>
						<chakra.th textAlign={"left"} p={2}>
							Mã phiếu
						</chakra.th>
						<chakra.th p={2}>Nhà cung cấp</chakra.th>
						<chakra.th p={2}>Thời gian nhập</chakra.th>
						<chakra.th p={2} textAlign={"right"}>
							Tổng tiền
						</chakra.th>
						<chakra.th p={2} textAlign={"right"}>
							Tiền cần trả
						</chakra.th>
					</>
				}
			>
				{render()}
			</Table>
		</Flex>
	)
}

export default ReturnImportHomeUI
