import { Box, Flex, Img, NumberInput, NumberInputField, Stack, Text } from "@chakra-ui/react"
import { currency } from "@helper"
import { useTheme } from "@hooks"
import DiscountInput from "./DiscountInput"
import QuantityChanger from "./QuantityChanger"
import useCreateImport from "./useCreateImport"

interface PurchaseItemProps {
	data: ReturnType<typeof useCreateImport>["mappedItems"][number]
}

const PurchaseItem = ({ data }: PurchaseItemProps) => {
	const theme = useTheme()

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
					<Text color={theme.textSecondary}>{data.item.barcode}</Text>
				</Box>
			</Flex>
			<QuantityChanger value={data.quantity} onChange={data.onChangeQuantity} />
			<NumberInput
				value={format(data.price)}
				min={0}
				step={1}
				onChange={(_, value) => data.onChangePrice(value)}
				w="8rem"
				variant={"filled"}
			>
				<NumberInputField textAlign={"right"} w="full" pr={4} />
			</NumberInput>
			<DiscountInput
				value={data.discount}
				onChange={data.onChangeDiscount}
				discountType={data.discount_type}
				onChangeDiscountType={data.onChangeDiscountType}
				maxCash={data.price}
			/>
			<Text ml="auto" w="8rem" textAlign={"right"} fontWeight={"semibold"}>
				{currency(data.total)}
			</Text>
		</Stack>
	)
}

export default PurchaseItem
