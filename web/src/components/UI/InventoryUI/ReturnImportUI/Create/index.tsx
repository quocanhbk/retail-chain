import {
	Box,
	Button,
	Flex,
	HStack,
	Input,
	InputGroup,
	InputLeftElement,
	NumberInput,
	NumberInputField,
	Stack,
	Text,
	Textarea,
	VStack
} from "@chakra-ui/react"
import { BackableTitle, LoadingOverlay, SubmitConfirmAlert } from "@components/shared"
import { currency } from "@helper"
import { BsTruck } from "react-icons/bs"
import { IoMdCheckmarkCircle, IoMdSettings, IoMdTrash } from "react-icons/io"
import DiscountInput from "./DiscountInput"
import NotFound from "./NotFound"
import ReturnPurchaseItem from "./ReturnPurchaseItem"
import Sidebar from "./Sidebar"
import useCreateReturnImport from "./useCreateReturnImport"

interface ReturnImportCreateUIProps {
	id?: number
}

const ReturnImportCreateUI = ({ id }: ReturnImportCreateUIProps) => {
	const {
		mappedItems,
		readOnly,
		data,
		isLoadingData,
		purchaseSheetQuery,
		values,
		setValue,
		total,
		needToPay,
		handleConfirmButtonClick,
		isLoading,
		notFound
	} = useCreateReturnImport(id)

	return (
		<Flex direction="column" p={4} h="full" pos="relative">
			<LoadingOverlay isLoading={isLoadingData} />
			{notFound && <NotFound />}
			<BackableTitle text={id ? "Xem phiếu trả hàng nhập" : "Tạo phiếu trả hàng nhập"} backPath="/main/inventory/return-import" />
			<Stack direction="row" flex={1} spacing={4} overflow="hidden">
				<VStack align="stretch" bg={"background.secondary"} p={4} rounded="md" flex={5} flexShrink={0} overflow="hidden" spacing={4}>
					<HStack spacing={6}>
						<Text flex={1}>Sản phẩm</Text>
						<Text w="6rem" textAlign={"center"}>
							Số lượng
						</Text>
						<Text w="8rem" textAlign={"center"}>
							Giá nhập
						</Text>
						<Text w="8rem" textAlign={"center"}>
							Giá trả lại
						</Text>
						<Text w="8rem" textAlign={"right"}>
							Thành tiền
						</Text>
					</HStack>
					{mappedItems.length > 0 ? (
						<VStack align="stretch" spacing={2}>
							{mappedItems.map(item => (
								<ReturnPurchaseItem key={item.item_id} data={item} readOnly={readOnly} />
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
							<InputGroup>
								<InputLeftElement>{<BsTruck />}</InputLeftElement>
								<Input
									w="full"
									value={
										purchaseSheetQuery.data?.supplier
											? `${purchaseSheetQuery.data?.supplier.name} - ${purchaseSheetQuery.data?.supplier.code}`
											: ""
									}
									background={"background.secondary"}
									placeholder="Tìm kiếm nhà cung cấp"
									readOnly={true}
									pr="3rem"
								/>
							</InputGroup>
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
									onChangeDiscountType={type => {
										setValue("discount", 0)
										setValue("discount_type", type)
									}}
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
						{!id && (
							<Button
								onClick={handleConfirmButtonClick}
								isLoading={isLoading}
								isDisabled={mappedItems.length === 0}
								leftIcon={<IoMdCheckmarkCircle />}
							>
								{"Xác nhận"}
							</Button>
						)}
					</VStack>
				</VStack>
			</Stack>
		</Flex>
	)
}

export default ReturnImportCreateUI
