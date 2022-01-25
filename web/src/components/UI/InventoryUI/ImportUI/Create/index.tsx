import { Box, Button, Divider, Flex, HStack, Input, NumberInput, NumberInputField, Stack, Text, Textarea, VStack } from "@chakra-ui/react"
import { BackableTitle, FormControl } from "@components/shared"
import { currency } from "@helper"
import { useTheme } from "@hooks"
import DiscountInput from "./DiscountInput"
import ItemSearchInput from "./ItemSearchInput"
import PurchaseItem from "./PurchaseItem"
import Sidebar from "./Sidebar"
import SupplierSearchInput from "./Sidebar/SupplierSearchInput"
import useCreateImport from "./useCreateImport"

const ImportCreateUI = () => {
	const { backgroundSecondary, fillPrimary, textSecondary } = useTheme()
	const {
		searchText,
		setSearchText,
		searchQuery,
		handleClickDefaultItem,
		handleClickItem,
		mappedItems,
		selectedSupplier,
		setSelectedSupplier,
		total,
		needToPay,
		values,
		setValue,
		mutateCreatePurchaseSheet,
		isLoading
	} = useCreateImport()
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
					{mappedItems.length > 0 ? (
						<VStack align="stretch" spacing={2}>
							{mappedItems.map(item => (
								<PurchaseItem key={item.item_id} data={item} />
							))}
						</VStack>
					) : (
						<Text textAlign="center" color={textSecondary}>
							Chưa có sản phẩm nào
						</Text>
					)}
				</VStack>
				<VStack align="stretch" flex={2} flexShrink={0} spacing={4}>
					<Sidebar>
						<VStack align="stretch" spacing={4}>
							<SupplierSearchInput selectedSupplier={selectedSupplier} onSelectSupplier={setSelectedSupplier} />
							<Flex align="center">
								<Text flex={1}>{"Mã phiếu nhập"}</Text>
								<Input
									placeholder="Mã phiếu tự động"
									w="8rem"
									ml={2}
									value={values.code}
									onChange={e => setValue("code", e.target.value)}
									variant={"filled"}
								/>
							</Flex>
							<Flex align="center">
								<Text flex={1}>Tổng tiền</Text>
								<Text ml={2}>{currency(total)}</Text>
							</Flex>
							<Flex align="center">
								<Text flex={1}>Giảm giá</Text>
								<DiscountInput
									w="8rem"
									ml={2}
									variant={"filled"}
									discountType={values.discount_type}
									onChangeDiscountType={type => setValue("discount_type", type)}
									value={values.discount}
									onChange={discount => setValue("discount", discount)}
									maxCash={needToPay}
								/>
							</Flex>
							<Flex align="center">
								<Text flex={1}>Tiền cần trả</Text>
								<Text ml={2} fontSize={"lg"} color={fillPrimary} fontWeight={"bold"}>
									{currency(needToPay)}
								</Text>
							</Flex>
							<Flex align="center">
								<Text flex={1}>Tiền trả trước</Text>
								<NumberInput
									w="8rem"
									ml={2}
									variant={"filled"}
									value={currency(values.paid_amount)}
									onChange={(_, value) => setValue("paid_amount", value)}
									min={0}
									max={needToPay}
								>
									<NumberInputField pr={4} textAlign={"right"} />
								</NumberInput>
							</Flex>
							<Box>
								<Text mb={1}>{"Ghi chú"}</Text>
								<Textarea
									value={values.note}
									onChange={e => setValue("note", e.target.value)}
									variant="filled"
									resize={"none"}
								/>
							</Box>
						</VStack>
					</Sidebar>
					<Button onClick={() => mutateCreatePurchaseSheet()} isLoading={isLoading} isDisabled={mappedItems.length === 0}>
						{"Xác nhận"}
					</Button>
				</VStack>
			</Stack>
		</Flex>
	)
}

export default ImportCreateUI
