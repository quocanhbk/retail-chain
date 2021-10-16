import { FormControl, FormLabel, Input, FormControlProps, FormErrorMessage } from "@chakra-ui/react"
import { ComponentProps } from "react"

interface TextControlProps extends Omit<FormControlProps, "onChange"> {
	label: string
	error?: string
	value?: ComponentProps<typeof Input>["value"]
	onChange?: (value: string) => void
	type?: ComponentProps<typeof Input>["type"]
}

export const TextControl = ({ label, error, value, onChange, type, ...rest }: TextControlProps) => {
	return (
		<FormControl isInvalid={!!error} mb={4} {...rest}>
			<FormLabel>{label}</FormLabel>
			<Input type={type} value={value} onChange={(e) => onChange && onChange(e.target.value)} variant="outline" />
			<FormErrorMessage>{error}</FormErrorMessage>
		</FormControl>
	)
}

export default TextControl
