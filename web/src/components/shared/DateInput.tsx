import { Flex, Input } from "@chakra-ui/react"
import { useTheme } from "@hooks"

export interface DateInput {
	day: number | null
	month: number | null
	year: number | null
}

interface DateInputProps {
	value: DateInput | null
	onChange: (value: DateInput | null) => void
	readOnly?: boolean
}

export const DateInput = ({ value, onChange, readOnly = false }: DateInputProps) => {
	const getEmptyDateInput = (): DateInput => ({
		day: null,
		month: null,
		year: null
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

	const { borderPrimary } = useTheme()

	return (
		<Flex h="2.5rem" border="1px" borderColor={borderPrimary} rounded="md" overflow={"hidden"}>
			<Input
				variant="unstyled"
				rounded="none"
				textAlign={"right"}
				px={4}
				placeholder="Ngày"
				type={"number"}
				value={value?.day || ""}
				onChange={handleChange("day")}
				borderRight={"1px"}
				borderColor={borderPrimary}
				readOnly={readOnly}
			/>

			<Input
				variant="unstyled"
				rounded="none"
				textAlign={"right"}
				px={4}
				placeholder="Tháng"
				type={"number"}
				value={value?.month || ""}
				onChange={handleChange("month")}
				borderRight={"1px"}
				borderColor={borderPrimary}
				readOnly={readOnly}
			/>

			<Input
				variant="unstyled"
				rounded="none"
				textAlign={"right"}
				px={4}
				placeholder="Năm"
				type={"number"}
				value={value?.year || ""}
				onChange={handleChange("year")}
				borderColor={borderPrimary}
				readOnly={readOnly}
			/>
		</Flex>
	)
}

export default DateInput
