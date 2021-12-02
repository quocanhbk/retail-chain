import { FormControl, FormLabel, Input, FormControlProps, FormErrorMessage } from "@chakra-ui/react"
import { ComponentProps } from "react"

interface TextControlProps extends Omit<FormControlProps, "onChange"> {
	label: string
	error?: string
	name?: string
	value?: ComponentProps<typeof Input>["value"]
	onChange?: (value: string) => void
	type?: ComponentProps<typeof Input>["type"]
	size?: ComponentProps<typeof Input>["size"]
}

export const TextControl = ({ label, error, value, onChange, type, name, size, ...rest }: TextControlProps) => {
	return (
		<FormControl isInvalid={!!error} mb={4} {...rest}>
			<FormLabel mb={1}>{label}</FormLabel>
			<Input
				type={type}
				name={name}
				value={value}
				onChange={e => onChange && onChange(e.target.value)}
				variant="outline"
				size={size}
			/>
			<FormErrorMessage>{error}</FormErrorMessage>
		</FormControl>
	)
}

export default TextControl
