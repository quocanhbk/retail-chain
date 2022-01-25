// import dataFetcher from "@api"
import { Box, Flex, Heading, Wrap, WrapItem, Text } from "@chakra-ui/react"
import { SearchInput } from "@components/shared"
import { useState } from "react"
import { BsPlus, BsX } from "react-icons/bs"
import { useQuery } from "react-query"
import AddNew from "./AddNew"
import Filter from "./Filter"
import ProductTable from "./ProductTable"
import Sorter from "./Sorter"


const ProductHomeUI = () => {
	const [isOpen, setIsOpen] = useState(false)
	const [deletingId, setDeletingId] = useState<number | null>(null)
	const [searchText, setSearchText] = useState("")
	// const { data } = useQuery("category", () => dataFetcher.getCategory())

	return (
		<Flex direction="row" w="full" px={4} my={5}>
			<Flex direction={"column"} justify="center" w="15rem" mr={6} h="full">
				<Box mb={4}>
					<Heading size={"lg"}>{"Hàng hóa"}</Heading>
				</Box>
				<Flex direction={"column"}>
					<Filter/>
				</Flex>
			</Flex>
			<Flex direction={"column"} flex={1}>
				<Flex direction={"row"} h="50px" justify={"space-between"}>
					<SearchInput
						value={searchText}
						onChange={e => setSearchText(e.target.value)}
						placeholder="Tìm kiếm hàng hóa"
						mb={2}
						w="25rem"
						onClear={() => setSearchText("")}
					/>
					<Flex direction={"row"}>
						<AddNew/>
					</Flex>
				</Flex>
				<Flex>
					<ProductTable />
					{/* <Text>content</Text> */}
				</Flex>
			</Flex>
		</Flex>
	)
}

export default ProductHomeUI
