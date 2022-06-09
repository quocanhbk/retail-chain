import { Box, Flex, Img, NumberInput, NumberInputField, Stack, Text } from "@chakra-ui/react"
import { currency } from "@helper"
import DiscountInput from "./DiscountInput"
import QuantityChanger from "./QuantityChanger"
import useCreateReturnImport from "./useCreateReturnImport"

interface ReturnPurchaseItemProps {
	data: ReturnType<typeof useCreateReturnImport>["mappedItems"][number]
	readOnly?: boolean
}

const ReturnPurchaseItem = ({ data, readOnly = false }: ReturnPurchaseItemProps) => {
	const format = (value: number) => {
		// add comma separator
		return value ? value.toLocaleString(undefined, { maximumFractionDigits: 0 }) : 0
	}

	return (
		<Stack direction="row" spacing={6} align="center">
			<Flex flex={1} overflow="hidden">
				<Img src={data.item.image} boxSize={"2.5rem"} rounded="md" />
				<Box ml={4} flex={1} overflow="hidden">
					<Text isTruncated>{data.item.name}</Text>
					<Text color={"text.secondary"}>{data.item.barcode}</Text>
				</Box>
			</Flex>
			<QuantityChanger value={data.quantity} onChange={data.onChangeQuantity} readOnly={readOnly} />
			<Box pos="relative">
				<NumberInput value={format(data.price)} min={0} step={1} w="8rem" variant={"filled"} isReadOnly={true}>
					<NumberInputField textAlign={"right"} w="full" pr={4} />
				</NumberInput>
			</Box>
			<DiscountInput
				value={data.return_price}
				onChange={data.onChangeReturnPrice}
				discountType={data.return_price_type}
				onChangeDiscountType={data.onChangeReturnPriceTye}
				maxCash={Infinity}
				isReadOnly={readOnly}
			/>
			<Text ml="auto" w="8rem" textAlign={"right"} fontWeight={"semibold"}>
				{currency(data.total)}
			</Text>
		</Stack>
	)
}

export default ReturnPurchaseItem
