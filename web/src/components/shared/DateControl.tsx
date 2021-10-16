import { FormControl, FormLabel, FormErrorMessage, FormControlProps } from "@chakra-ui/react"
import { Calendar } from "."

interface DateControlProps extends Omit<FormControlProps, "onChange"> {
	value: string
	onChange: (nextValue: string) => void
	error?: string
	label: string
}

export const DateControl = ({ value, onChange, error, label, ...rest }: DateControlProps) => {
	return (
		<FormControl isInvalid={!!error} mb={4} {...rest}>
			<FormLabel>{label}</FormLabel>
			<Calendar value={value} onChange={onChange} />
			<FormErrorMessage>{error}</FormErrorMessage>
		</FormControl>
	)
}

export default DateControl
