import { Box, Flex, Heading, Wrap, WrapItem, Text, Button, Menu, MenuButton, MenuList, MenuItem } from "@chakra-ui/react"
import { SearchInput } from "@components/shared"
import Link from "next/link"
import { FaFileImport } from "react-icons/fa"
import { IoIosArrowDown } from "react-icons/io"
import AddNewProductModal from "./AddNewProductModal"
import ProductCard from "./ProductCard/ProductCard"
import ProductCardSkeleton from "./ProductCard/ProductCardSkeleton"
import useProductHome from "./useProductHome"

const ProductHomeUI = () => {
	const { allItemQuery, search, setSearch, setIsClose, isClose, searchQueryCurrent } = useProductHome()
	const { data: items, isLoading: isLoadingItems, isError } = allItemQuery
	const { data: itemsCurrent, isLoading: isLoadingItemsCurrent, isError: isErrorItemsCurrent } = searchQueryCurrent
	// const currentItems = itemsCurrent && search !== ""? itemsCurrent.currentItems : items
	const isLoading = isLoadingItems

	const render = () => {
		if (isLoading) {
			return (
				<Wrap spacing={2}>
					{[...Array(10)].map((_, i) => (
						<WrapItem key={i}>
							<ProductCardSkeleton />
						</WrapItem>
					))}
				</Wrap>
			)
		}
		if (isError || !items) {
			return <Text>{"Có lỗi xảy ra, vui lòng thử lại sau"}</Text>
		}
		return (
			<Wrap spacing={2}>
				{items.map((item, index) => (
					<WrapItem key={item.id}>
						<ProductCard data={item} index={index} />
					</WrapItem>
				))}
			</Wrap>
		)
	}

	return (
		<Box p={4}>
			<Box w="full">
				<Flex w="full" align="center" justify="space-between" mb={4}>
					<Heading fontSize={"2xl"}>Hàng hóa</Heading>
					<Flex direction={"row"}>
						<Menu>
							<MenuButton as={Button} size={"sm"} rightIcon={<IoIosArrowDown />}>
								<Text>{"Thêm mới"}</Text>
							</MenuButton>
							<MenuList py={1} zIndex={10}>
								<MenuItem py={1} px={2}>
									<Link href={"/main/inventory/import/create"}>{"Nhập hàng"}</Link>
								</MenuItem>
								<MenuItem py={1} px={2} onClick={() => setIsClose(false)}>
									{"Thêm hàng hóa"}
								</MenuItem>
							</MenuList>
						</Menu>
						<Button size={"sm"} ml={3}>
							<FaFileImport />
							<Text ml={3}>{"Import"}</Text>
						</Button>
					</Flex>
				</Flex>
				<SearchInput
					value={search}
					onChange={e => setSearch(e.target.value)}
					mb={4}
					placeholder="Tìm kiếm hàng hóa"
					onClear={() => setSearch("")}
				/>
				<Box>{render()}</Box>
			</Box>
			<AddNewProductModal isOpen={isClose !== true} onClose={() => setIsClose(true)} />
		</Box>
	)
}

export default ProductHomeUI
