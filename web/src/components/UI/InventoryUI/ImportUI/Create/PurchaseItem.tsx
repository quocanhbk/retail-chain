import { getLastPurchasePrice } from "@api"
import { Box, Flex, IconButton, Img, NumberInput, NumberInputField, Stack, Text, Tooltip } from "@chakra-ui/react"
import { currency } from "@helper"
import { useTheme } from "@hooks"
import { useState } from "react"
import { BsX } from "react-icons/bs"
import { useQuery } from "react-query"
import DiscountInput from "./DiscountInput"
import QuantityChanger from "./QuantityChanger"
import useCreateImport from "./useCreateImport"
import { IoIosWarning } from "react-icons/io"

interface PurchaseItemProps {
	data: ReturnType<typeof useCreateImport>["mappedItems"][number]
	readOnly?: boolean
}

const PurchaseItem = ({ data, readOnly = false }: PurchaseItemProps) => {
	const theme = useTheme()

	const format = (value: number) => {
		// add comma separator
		return value ? value.toLocaleString(undefined, { maximumFractionDigits: 0 }) : 0
	}

	const [invalid, setInvalid] = useState(false)

	const [isBlur, setIsBlur] = useState(false)

	const { data: oldPrice } = useQuery(["getLastPurchasePrice", data.item.id], () => getLastPurchasePrice(data.item_id), {
		enabled: isBlur,
		onSuccess: oldPrice => {
			setInvalid(oldPrice > 0 && Math.abs(oldPrice - data.price) > oldPrice / 2)
		}
	})

	return (
		<Stack direction="row" spacing={6} align="center">
			<Flex flex={1} overflow="hidden">
				<Img src={data.item.image} boxSize={"2.5rem"} rounded="md" />
				<Box ml={4} flex={1} overflow="hidden">
					<Text isTruncated>{data.item.name}</Text>
					<Text color={theme.textSecondary}>{data.item.barcode}</Text>
				</Box>
			</Flex>
			<QuantityChanger value={data.quantity} onChange={data.onChangeQuantity} readOnly={readOnly} />
			<Box pos="relative">
				{invalid && (
					<Tooltip
						label={`Giá chênh lệnh lớn so với lần nhập trước: ${currency(oldPrice || 0)}`}
						aria-label="A tooltip"
						background={theme.backgroundThird}
						color={theme.textPrimary}
						fontWeight={400}
						maxW="10rem"
						openDelay={500}
					>
						<Box color={theme.fillDanger} pos="absolute" left="0.5rem" top="50%" transform="translateY(-50%)" zIndex={2}>
							<IoIosWarning />
						</Box>
					</Tooltip>
				)}
				<NumberInput
					value={format(data.price)}
					min={0}
					step={1}
					onChange={(_, value) => data.onChangePrice(value)}
					w="8rem"
					variant={"filled"}
					isReadOnly={readOnly}
					onFocus={() => setIsBlur(false)}
					onBlur={() => setIsBlur(true)}
					isInvalid={invalid}
				>
					<NumberInputField textAlign={"right"} w="full" pr={4} />
				</NumberInput>
			</Box>
			<DiscountInput
				value={data.discount}
				onChange={data.onChangeDiscount}
				discountType={data.discount_type}
				onChangeDiscountType={data.onChangeDiscountType}
				maxCash={data.price}
				isReadOnly={readOnly}
			/>
			<Text ml="auto" w="8rem" textAlign={"right"} fontWeight={"semibold"}>
				{currency(data.total)}
			</Text>
			<IconButton
				aria-label="delete"
				icon={<BsX size="1.25rem" />}
				onClick={data.onRemove}
				rounded="full"
				size="sm"
				variant="ghost"
				colorScheme={"red"}
				isDisabled={readOnly}
			/>
		</Stack>
	)
}

export default PurchaseItem
