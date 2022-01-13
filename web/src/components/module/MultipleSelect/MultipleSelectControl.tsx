import { FormControl, FormLabel, FormErrorMessage, FormControlProps } from "@chakra-ui/react"
import { ComponentProps } from "react"
import MultipleSelect from "./MultipleSelect"

interface MultipleSelectControlProps extends ComponentProps<typeof MultipleSelect> {
	formProps?: FormControlProps
	error?: string
	label: string
}

export const MultipleSelectControl = ({
	label,
	error,
	formProps,
	...multipleSelectProps
}: MultipleSelectControlProps) => {
	return (
		<FormControl isInvalid={!!error} mb={4} w="full" {...formProps}>
			<FormLabel mb={1}>{label}</FormLabel>
			<MultipleSelect {...multipleSelectProps} />
			<FormErrorMessage>{error}</FormErrorMessage>
		</FormControl>
	)
}

export default MultipleSelectControl
