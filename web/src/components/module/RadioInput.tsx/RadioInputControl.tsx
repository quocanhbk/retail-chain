import { FormControl, FormLabel, FormErrorMessage, FormControlProps } from "@chakra-ui/react"
import RadioControl, { RadioControlProps } from "./RadioControl"

interface RadioInputControlProps extends RadioControlProps {
	label: string
	error?: string
	formProps?: FormControlProps
}

const RadioInputControl = ({ label, error, formProps, ...rest }: RadioInputControlProps) => {
	return (
		<FormControl isInvalid={!!error} mb={4} w="full" {...formProps}>
			<FormLabel mb={1}>{label}</FormLabel>
			<RadioControl {...rest} />
			<FormErrorMessage>{error}</FormErrorMessage>
		</FormControl>
	)
}

export default RadioInputControl
