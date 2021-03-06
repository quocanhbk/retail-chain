import { Box, Button, Flex, HStack, Input, NumberInput, NumberInputField, Stack, Text, Textarea, VStack } from "@chakra-ui/react"
import { BackableTitle, LoadingOverlay, SubmitConfirmAlert } from "@components/shared"
import { currency } from "@helper"
import { useRouter } from "next/router"
import { BsThreeDots } from "react-icons/bs"
import { IoMdCheckmarkCircle, IoMdReturnLeft, IoMdSettings, IoMdTrash } from "react-icons/io"
import DiscountInput from "./DiscountInput"
import ItemSearchInput from "./ItemSearchInput"
import PurchaseItem from "./PurchaseItem"
import Sidebar from "./Sidebar"
import SupplierSearchInput from "./Sidebar/SupplierSearchInput"
import useCreateImport from "./useCreateImport"
import useSearchQuery from "./useSearchQuery"

interface ImportCreateUIProps {
	id?: number
}

const ImportCreateUI = ({ id }: ImportCreateUIProps) => {
	const router = useRouter()

	const {
		handleClickDefaultItem,
		handleClickItem,
		mappedItems,
		selectedSupplier,
		setSelectedSupplier,
		total,
		needToPay,
		values,
		setValue,
		isLoading,
		readOnly,
		handleConfirmButtonClick,
		data,
		isLoadingData
	} = useCreateImport(id)

	const { searchText, setSearchText, searchQuery } = useSearchQuery()

	return (
		<Flex direction="column" p={4} h="full" pos="relative">
			<LoadingOverlay isLoading={isLoadingData} />
			<BackableTitle text={id ? "Xem phiếu nhập hàng" : "Tạo phiếu nhập hàng"} backPath="/main/inventory/import" />
			<Stack direction="row" flex={1} spacing={4} overflow="hidden">
				<VStack align="stretch" bg={"background.secondary"} p={4} rounded="md" flex={5} flexShrink={0} overflow="hidden" spacing={4}>
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
						<Box px={2}>
							<BsThreeDots />
						</Box>
					</HStack>
					{mappedItems.length > 0 ? (
						<VStack align="stretch" spacing={2}>
							{mappedItems.map(item => (
								<PurchaseItem key={item.item_id} data={item} readOnly={readOnly} />
							))}
						</VStack>
					) : (
						<Text textAlign="center" color={"text.secondary"}>
							Chưa có sản phẩm nào
						</Text>
					)}
				</VStack>
				<VStack align="stretch" flex={2} flexShrink={0} spacing={4}>
					<Sidebar>
						<VStack align="stretch" spacing={4}>
							<SupplierSearchInput selectedSupplier={selectedSupplier} onSelectSupplier={setSelectedSupplier} readOnly={!!id} />
							<Flex align="center">
								<Text flex={1}>{"Mã phiếu nhập"}</Text>
								<Input
									placeholder="Mã phiếu tự động"
									w="8rem"
									ml={2}
									value={values.code}
									onChange={e => setValue("code", e.target.value)}
									variant={"filled"}
									readOnly={!!id}
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
									readOnly={readOnly}
								/>
							</Flex>
							<Flex align="center">
								<Text flex={1}>Tiền cần trả</Text>
								<Text ml={2} fontSize={"lg"} color={"fill.primary"} fontWeight={"bold"}>
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
									isReadOnly={readOnly}
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
									isReadOnly={readOnly}
								/>
							</Box>
						</VStack>
					</Sidebar>
					<VStack align="stretch" pt={4} borderTop="1px" borderColor={"border.primary"} spacing={4}>
						<Button
							onClick={handleConfirmButtonClick}
							isLoading={isLoading}
							isDisabled={mappedItems.length === 0}
							leftIcon={readOnly ? <IoMdSettings /> : <IoMdCheckmarkCircle />}
						>
							{id && readOnly ? "Chỉnh sửa" : "Xác nhận"}
						</Button>
						{readOnly && (
							<>
								<Button
									leftIcon={<IoMdReturnLeft />}
									variant="outline"
									onClick={() => router.push(`/main/inventory/return-import/create?purchase-sheet=${id}`)}
								>
									{"Trả hàng nhập"}
								</Button>
							</>
						)}
					</VStack>
				</VStack>
			</Stack>
		</Flex>
	)
}

export default ImportCreateUI
