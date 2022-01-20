import { DateInput } from "@components/module/DateInput.tsx"

export const isEmail = (email: string) => {
	const re =
		/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
	return re.test(String(email).toLowerCase())
}

export const dateToDateInput = (date: string | null): DateInput | null => {
	if (!date) {
		return null
	}

	const dateObj = new Date(date)
	return {
		year: dateObj.getFullYear(),
		month: dateObj.getMonth() + 1,
		day: dateObj.getDate()
	}
}
