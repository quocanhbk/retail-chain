import { Box, Flex, HStack, Stack, Text, VStack } from "@chakra-ui/react"
import { BackableTitle } from "@components/shared"
import { useTheme } from "@hooks"
import ItemSearchInput from "./ItemSearchInput"
import PurchaseItem from "./PurchaseItem"
import Sidebar from "./Sidebar"
import useCreateImport from "./useCreateImport"

const ImportCreateUI = () => {
	const { backgroundSecondary } = useTheme()
	const { searchText, setSearchText, searchQuery, handleClickDefaultItem, handleClickItem, mappedValues } = useCreateImport()
	return (
		<Flex direction="column" p={4} h="full">
			<BackableTitle text="Tạo phiếu nhập hàng" backPath="/main/inventory/import" />
			<Stack direction="row" flex={1} spacing={4} overflow="hidden">
				<VStack align="stretch" bg={backgroundSecondary} p={4} rounded="md" flex={5} flexShrink={0} overflow="hidden" spacing={4}>
					<ItemSearchInput
						searchText={searchText}
						setSearchText={setSearchText}
						searchQuery={searchQuery}
						onDefaultItemClick={handleClickDefaultItem}
						onItemClick={handleClickItem}
					/>
					<HStack spacing={6}>
						<Text flex={1}>Sản phẩm</Text>
						<Text w="6rem" textAlign={"center"}>
							Số lượng
						</Text>
						<Text w="8rem" textAlign={"center"}>
							Đơn giá
						</Text>
						<Text w="8rem" textAlign={"center"}>
							Giảm giá
						</Text>
						<Text w="8rem" textAlign={"right"}>
							Thành tiền
						</Text>
					</HStack>
					<VStack align="stretch" spacing={2}>
						{mappedValues.map(item => (
							<PurchaseItem key={item.item_id} data={item} />
						))}
					</VStack>
				</VStack>
				<Sidebar />
			</Stack>
		</Flex>
	)
}

export default ImportCreateUI
