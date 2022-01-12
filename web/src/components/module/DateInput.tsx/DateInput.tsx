import { Flex, Input } from "@chakra-ui/react"

export interface DateInput {
	day: number | null
	month: number | null
	year: number | null
}

interface DateInputProps {
	value: DateInput | null
	onChange: (value: DateInput | null) => void
}

export const DateInput = ({ value, onChange }: DateInputProps) => {
	const getEmptyDateInput = (): DateInput => ({
		day: null,
		month: null,
		year: null,
	})

	const isEmptyDateInput = (dateInput: DateInput): boolean => {
		return dateInput.day === null && dateInput.month === null && dateInput.year === null
	}

	const handleChange = (field: keyof DateInput) => (e: React.ChangeEvent<HTMLInputElement>) => {
		if (value === null) {
			const newDate = getEmptyDateInput()
			// set value if not empty, else set null
			newDate[field] = e.target.value !== "" ? parseInt(e.target.value) : null
			onChange(isEmptyDateInput(newDate) ? null : newDate)
		} else {
			// set value if not empty, else set null
			const newDate = { ...value, [field]: e.target.value !== "" ? parseInt(e.target.value) : null }
			onChange(isEmptyDateInput(newDate) ? null : newDate)
		}
	}

	return (
		<Flex h="2.5rem" border="1px" borderColor={"gray.200"} bg="white" rounded="md">
			<Input
				variant="unstyled"
				rounded="none"
				textAlign={"right"}
				px={4}
				_notLast={{
					borderRight: "1px",
					borderColor: "gray.200",
				}}
				placeholder="Ngày"
				type={"number"}
				value={value?.day || ""}
				onChange={handleChange("day")}
			/>

			<Input
				variant="unstyled"
				rounded="none"
				textAlign={"right"}
				px={4}
				_notLast={{
					borderRight: "1px",
					borderColor: "gray.200",
				}}
				placeholder="Tháng"
				type={"number"}
				value={value?.month || ""}
				onChange={handleChange("month")}
			/>

			<Input
				variant="unstyled"
				rounded="none"
				textAlign={"right"}
				px={4}
				_notLast={{
					borderRight: "1px",
					borderColor: "gray.200",
				}}
				placeholder="Năm"
				type={"number"}
				value={value?.year || ""}
				onChange={handleChange("year")}
			/>
		</Flex>
	)
}

export default DateInput
