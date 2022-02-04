import { Box, Button, Flex, Heading, Text, SimpleGrid } from "@chakra-ui/react"
import { SearchInput } from "@components/shared"
import { useQuery } from "react-query"
import { useState } from "react"
import Link from "next/link"
import { getSuppliers } from "@api"
import SupplierCardSkeleton from "./SupplierCardSkeleton"
import SupplierCard from "./SupplierCard"

const HomeSupplierUI = () => {
	const { data: suppliers, isLoading, isError } = useQuery("suppliers", () => getSuppliers())
	const [searchText, setSearchText] = useState("")

	const render = () => {
		if (isLoading)
			return (
				<SimpleGrid columns={4} spacing={4}>
					{Array(8)
						.fill(null)
						.map((_, index) => (
							<SupplierCardSkeleton key={index} />
						))}
				</SimpleGrid>
			)
		if (isError || !suppliers) return <Box>Error</Box>

		if (suppliers.length === 0) return <Text color={"text.secondary"}>{"Không có nhà cung cấp nào!"}</Text>

		return (
			<SimpleGrid columns={4}>
				{suppliers
					.filter(supplier => `${supplier.name.toLowerCase()} , ${supplier.phone} , ${supplier.email}`.indexOf(searchText) !== -1)
					.map((supplier, index) => (
						<SupplierCard key={index} data={supplier} />
					))}
			</SimpleGrid>
		)
	}

	return (
		<Box p={4}>
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
			{render()}
		</Box>
	)
}

export default HomeSupplierUI
