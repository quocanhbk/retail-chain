import { Box, Flex, NumberInput, NumberInputField, NumberInputProps } from "@chakra-ui/react"
import { currency } from "@helper"

interface DiscountInputProps extends Omit<NumberInputProps, "value" | "onChange"> {
	value: number
	onChange: (value: number) => void
	discountType: "cash" | "percent"
	onChangeDiscountType: (type: "cash" | "percent") => void
	maxCash: number
	readOnly?: boolean
}

const DiscountInput = ({ value, onChange, discountType, maxCash, onChangeDiscountType, readOnly = false, ...rest }: DiscountInputProps) => {
	// toggle discount type
	const toggleDiscountType = () => {
		onChangeDiscountType(discountType === "cash" ? "percent" : "cash")
	}

	return (
		<NumberInput
			value={currency(value)}
			onChange={(_, value) => onChange(value)}
			min={0}
			step={discountType === "cash" ? 1 : 0.01}
			max={discountType === "cash" ? maxCash : 100}
			pos="relative"
			h="2.5rem"
			w="8rem"
			variant={"filled"}
			isReadOnly={readOnly}
			{...rest}
		>
			<NumberInputField textAlign={"right"} pr={10} />
			<Flex pos="absolute" right={0} top={0} h="full" zIndex={1}>
				<Box
					w="2rem"
					textAlign={"center"}
					py={2}
					borderLeft={"1px"}
					borderColor={"border.primary"}
					cursor="pointer"
					onClick={toggleDiscountType}
					title={discountType === "cash" ? "Giảm tiền mặt" : "Giảm phần trăm"}
				>
					{discountType === "cash" ? "đ" : "%"}
				</Box>
			</Flex>
		</NumberInput>
	)
}

export default DiscountInput
