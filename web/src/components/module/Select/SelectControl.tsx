import { FormControl, FormLabel, FormErrorMessage, FormControlProps } from "@chakra-ui/react"
import { ComponentProps } from "react"
import Select from "./Select"

interface SelectControlProps extends ComponentProps<typeof Select> {
	formProps?: FormControlProps
	error?: string
	label: string
}

export const SelectControl = ({ label, error, formProps, ...multipleSelectProps }: SelectControlProps) => {
	return (
		<FormControl isInvalid={!!error} mb={4} w="full" {...formProps}>
			<FormLabel mb={1}>{label}</FormLabel>
			<Select {...multipleSelectProps} />
			<FormErrorMessage>{error}</FormErrorMessage>
		</FormControl>
	)
}

export default SelectControl
