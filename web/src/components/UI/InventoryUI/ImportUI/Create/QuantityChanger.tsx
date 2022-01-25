import { NumberDecrementStepper, NumberIncrementStepper, NumberInput, NumberInputField, NumberInputStepper } from "@chakra-ui/react"

interface QuantityChangerProps {
	value: number
	onChange: (value: number) => void
}

const QuantityChanger = ({ value, onChange }: QuantityChangerProps) => {
	return (
		<NumberInput
			value={value}
			min={0}
			step={1}
			onChange={(_, value) => onChange(value)}
			h="2.5rem"
			w="6rem"
			flexShrink={0}
			variant={"filled"}
		>
			<NumberInputField textAlign={"right"} />
			<NumberInputStepper>
				<NumberIncrementStepper />
				<NumberDecrementStepper />
			</NumberInputStepper>
		</NumberInput>
	)
}

export default QuantityChanger
