import { FormControl, FormLabel, Input, FormControlProps, FormErrorMessage } from "@chakra-ui/react"
import { ComponentProps, forwardRef } from "react"

interface TextControlProps extends Omit<FormControlProps, "onChange"> {
	label: string
	error?: string
	name?: string
	value?: ComponentProps<typeof Input>["value"]
	onChange: ComponentProps<typeof Input>["onChange"]
	type?: ComponentProps<typeof Input>["type"]
	size?: ComponentProps<typeof Input>["size"]
	readOnly?: boolean
}

export const TextControl = forwardRef<HTMLInputElement, TextControlProps>(
	({ label, error, value, onChange, type, name, size, readOnly, ...rest }, ref) => {
		return (
			<FormControl isInvalid={!!error} mb={4} w="full" {...rest}>
				<FormLabel mb={1}>{label}</FormLabel>
				<Input
					type={type}
					name={name}
					value={value}
					onChange={onChange}
					variant={"outline"}
					size={size}
					w="full"
					ref={ref}
					backgroundColor={"background.secondary"}
					readOnly={readOnly}
				/>
				<FormErrorMessage>{error}</FormErrorMessage>
			</FormControl>
		)
	}
)

export default TextControl
