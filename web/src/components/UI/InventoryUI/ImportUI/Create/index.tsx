import { Box, Flex, Stack, VStack } from "@chakra-ui/react"
import { BackableTitle } from "@components/shared"
import { useTheme } from "@hooks"
import ItemSearchInput from "./ItemSearchInput"
import PurchaseItem from "./PurchaseItem"
import useCreateImport from "./useCreateImport"

const ImportCreateUI = () => {
	const { backgroundSecondary } = useTheme()
	const { searchText, setSearchText, searchQuery, values, handleClickDefaultItem, handleClickItem, mappedValues } = useCreateImport()
	return (
		<Flex direction="column" p={4} h="full">
			<BackableTitle text="Tạo phiếu nhập hàng" backPath="/main/inventory/import" />
			<Stack direction="row" flex={1} spacing={4}>
				<VStack align="stretch" bg={backgroundSecondary} p={4} rounded="md" flex={3} spacing={4}>
					<ItemSearchInput
						searchText={searchText}
						setSearchText={setSearchText}
						searchQuery={searchQuery}
						onDefaultItemClick={handleClickDefaultItem}
						onItemClick={handleClickItem}
					/>
					<Box>
						{mappedValues.map(item => (
							<PurchaseItem key={item.item_id} data={item} />
						))}
					</Box>
				</VStack>
				<Box bg={backgroundSecondary} p={4} rounded="md" flex={1}>
					side
				</Box>
			</Stack>
		</Flex>
	)
}

export default ImportCreateUI
