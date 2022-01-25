import { NumberInput, NumberInputField, NumberInputStepper, NumberIncrementStepper, NumberDecrementStepper } from "@chakra-ui/react"

interface QuantityChangerProps {
	value: number
	onChange: (value: number) => void
}

const QuantityChanger = ({ value, onChange }: QuantityChangerProps) => {
	return (
		<NumberInput value={value} min={0} step={1} onChange={(_, value) => onChange(value)}>
			<NumberInputField />
			<NumberInputStepper>
				<NumberIncrementStepper />
				<NumberDecrementStepper />
			</NumberInputStepper>
		</NumberInput>
	)
}

export default QuantityChanger
