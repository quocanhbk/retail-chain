import {
	FormControl as ChakraFormControl,
	FormErrorMessage,
	FormLabel,
	FormControlProps as ChakraFormControlProps
} from "@chakra-ui/react"

interface FormControlProps extends ChakraFormControlProps {
	label: string
	error?: string
	children: React.ReactNode
}

export const FormControl = ({ label, children, error, ...rest }: FormControlProps) => {
	return (
		<ChakraFormControl isInvalid={!!error} mb={4} w="full" {...rest}>
			<FormLabel mb={1}>{label}</FormLabel>
			{children}
			<FormErrorMessage>{error}</FormErrorMessage>
		</ChakraFormControl>
	)
}

export default FormControl
