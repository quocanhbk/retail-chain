import { Box, Button, Flex, Heading, VStack, Text, Accordion, AccordionItem, AccordionButton, AccordionPanel, AccordionIcon} from "@chakra-ui/react"
import { SearchInput } from "@components/shared"
import { useQuery } from "react-query"
import { useState } from "react"
import Link from "next/link"
import { getSuppliers } from "@api"
import SupplierCardSkeleton from "./SupplierCardSkeleton"
import SupplierCard from "./SupplierCard"

const HomeSupplierUI = () => {
	const {data: suppliers, isLoading } =  useQuery("supplier", () => getSuppliers())
	const isError = !suppliers
	const [searchText, setSearchText] = useState("")

	const render = () => {
		if (isLoading)
			return (
				<VStack align="stretch">
					{Array(5)
						.fill(null)
						.map((_, index) => (
							<SupplierCardSkeleton key={index} />
						))}
				</VStack>
			)
		if (isError) return <Box>Error</Box>
		if (suppliers.length === 0)
			return (
				<Text color="blackAlpha.600" fontSize={"sm"}>
					{"Không có nhà cung cấp nào!"}
				</Text>
			)
		return (
			<Accordion allowMultiple>
				{suppliers
					.filter(supplier => (
						`${supplier.name.toLowerCase()} , ${supplier.phone} , ${supplier.email}`.indexOf(searchText) !== -1
					))
					.map((supplier, index) => (
						<SupplierCard
							key={index}
							data={supplier}
						/>
				))}
			</Accordion>
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
				mb={2}
				onClear={() => setSearchText("")}
			/>
			{render()}
		</Box>
	)
}

export default HomeSupplierUI
