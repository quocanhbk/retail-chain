import { FormControl, FormLabel, FormErrorMessage, FormControlProps } from "@chakra-ui/react"
import { ComponentProps } from "react"
import DateInput from "./DateInput"

interface DateInputControlProps extends ComponentProps<typeof DateInput> {
	formProps?: FormControlProps
	error?: string
	label: string
}

export const DateInputControl = ({ label, error, formProps, ...dateInputProps }: DateInputControlProps) => {
	return (
		<FormControl isInvalid={!!error} mb={4} w="full" {...formProps} onBlur={() => console.log("nice")}>
			<FormLabel mb={1}>{label}</FormLabel>
			<DateInput {...dateInputProps} />
			<FormErrorMessage>{error}</FormErrorMessage>
		</FormControl>
	)
}

export default DateInputControlProps
