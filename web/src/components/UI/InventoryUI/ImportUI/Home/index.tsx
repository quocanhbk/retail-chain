import { Box, Button, chakra, Flex, Heading, Stack, Text } from "@chakra-ui/react"
import Link from "next/link"
import useImportHome from "./useImportHome"
import PurchaseSheetCard from "./PurchaseSheetCard"
import PurchaseSheetCardSkeleton from "./PurchaseSheetCardSkeleton"
import { SearchInput, Sorter, Table } from "@components/shared"
import { purchaseSheetSorts } from "@constants"
import { BsPlus } from "react-icons/bs"
import { FaPlus } from "react-icons/fa"
import { BiPlus } from "react-icons/bi"

const ImportHomeUI = () => {
	const { purchaseSheetsQuery, search, setSearch, currentSort, setCurrentSort } = useImportHome()
	const { data, isLoading, isError } = purchaseSheetsQuery

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

		return data.map(ps => <PurchaseSheetCard key={ps.id} data={ps} />)
	}

	return (
		<Flex direction="column" p={4} h="full">
			<Flex w="full" align="center" justify="space-between" mb={4}>
				<Heading fontSize={"2xl"}>Nhập hàng</Heading>
				<Link href="/main/inventory/import/create">
					<Button size="sm" leftIcon={<BiPlus size="1.25rem" />}>
						{"Tạo phiếu mới"}
					</Button>
				</Link>
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

export default ImportHomeUI
