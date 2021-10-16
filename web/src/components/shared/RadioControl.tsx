import { Stack, RadioGroup, Radio, FormControl, FormLabel, FormErrorMessage, FormControlProps } from "@chakra-ui/react"

interface RadioControlProps extends Omit<FormControlProps, "onChange"> {
	value: string
	onChange?: (nextValue: string) => void
	data: RadioData[]
	error?: string
	label: string
}

interface RadioData {
	value: string
	text: string
}

export const RadioControl = ({ value, onChange, data, error, label, ...rest }: RadioControlProps) => {
	return (
		<FormControl isInvalid={!!error} mb={4} {...rest}>
			<FormLabel>{label}</FormLabel>
			<RadioGroup onChange={onChange} value={value}>
				<Stack
					direction="row"
					spacing={8}
					justify="space-around"
					border="1px"
					borderColor="gray.200"
					p={2}
					rounded="lg"
				>
					{data.map((item) => (
						<Radio key={item.value} value={item.value}>
							{item.text}
						</Radio>
					))}
				</Stack>
			</RadioGroup>
			<FormErrorMessage>{error}</FormErrorMessage>
		</FormControl>
	)
}

export default RadioControl
