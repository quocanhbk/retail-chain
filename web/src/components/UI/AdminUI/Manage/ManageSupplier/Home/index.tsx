import { Box, Button, Flex, Heading, Text, SimpleGrid, chakra, HStack, IconButton } from "@chakra-ui/react"
import { SearchInput, Table } from "@components/shared"
import { useQuery } from "react-query"
import { useState } from "react"
import Link from "next/link"
import { getSuppliers } from "@api"
import SupplierCardSkeleton from "./SupplierCardSkeleton"
import SupplierCard from "./SupplierCard"
import { useThrottle } from "@hooks"

const HomeSupplierUI = () => {
	const [searchText, setSearchText] = useState("")
	const throttledSearchText = useThrottle(searchText, 500)
	const { data: suppliers, isLoading, isError } = useQuery(["suppliers", throttledSearchText], () => getSuppliers(throttledSearchText))

	const render = () => {
		if (isLoading)
			return Array(8)
				.fill(null)
				.map((_, index) => <SupplierCardSkeleton key={index} />)

		if (isError || !suppliers)
			return (
				<chakra.tr>
					<chakra.td colSpan={4} textAlign={"center"}>
						<Text color="text.secondary">Error</Text>
					</chakra.td>
				</chakra.tr>
			)

		if (suppliers.length === 0)
			return (
				<chakra.tr>
					<chakra.td colSpan={4} textAlign={"center"}>
						<Text color="text.secondary">No data</Text>
					</chakra.td>
				</chakra.tr>
			)

		return suppliers.map(supplier => <SupplierCard key={supplier.id} data={supplier} />)
	}

	return (
		<Flex direction="column" p={4} h="full">
			<Flex w="full" align="center" justify="space-between">
				<Heading mb={4} fontSize={"2xl"}>
					{"Quản lý nhà cung cấp"}
				</Heading>

				<Link href="/admin/manage/supplier/create">
					<Button size="sm" variant="ghost">
						{"Tạo nhà cung cấp"}
					</Button>
				</Link>
			</Flex>
			<SearchInput
				value={searchText}
				onChange={e => setSearchText(e.target.value)}
				placeholder="Tìm kiếm nhà cung cấp"
				mb={4}
				onClear={() => setSearchText("")}
			/>
			<Table
				header={
					<>
						<chakra.th w="8rem">
							<Text fontWeight={"bold"}>{"Mã"}</Text>
						</chakra.th>
						<chakra.th>
							<Text fontWeight={"bold"}>{"Tên"}</Text>
						</chakra.th>
						<chakra.th>
							<Text fontWeight={"bold"}>{"Số diện thoại"}</Text>
						</chakra.th>
						<chakra.th>
							<Text fontWeight={"bold"}>{"Email"}</Text>
						</chakra.th>
					</>
				}
			>
				{render()}
			</Table>
		</Flex>
	)
}

export default HomeSupplierUI
