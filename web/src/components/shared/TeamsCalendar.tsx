import { useState, useRef } from "react"
import { BsCalendar, BsChevronLeft, BsChevronRight } from "react-icons/bs"
import { Box, Collapse, Flex, Grid, IconButton, Input, Text, useOutsideClick, Wrap } from "@chakra-ui/react"

const calendarData = {
	dayName: ["Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
	months: [
		{ id: 0, name: "January" },
		{ id: 1, name: "February" },
		{ id: 2, name: "March" },
		{ id: 3, name: "April" },
		{ id: 4, name: "May" },
		{ id: 5, name: "June" },
		{ id: 6, name: "July" },
		{ id: 7, name: "August" },
		{ id: 8, name: "September" },
		{ id: 9, name: "October" },
		{ id: 10, name: "November" },
		{ id: 11, name: "December" },
	],
	days: [
		{ id: 0, name: "Sunday" },
		{ id: 1, name: "Monday" },
		{ id: 2, name: "Tuesday" },
		{ id: 3, name: "Wednesday" },
		{ id: 4, name: "Thursday" },
		{ id: 5, name: "Friday" },
		{ id: 6, name: "Saturday" },
	],
}

interface IDateItem {
	id: number
	month: "previous" | "current" | "next"
	value: number
}

interface ICalendar {
	value: string | null
	onChange: (newDate: string) => void
}

const useCalendar = ({ value, onChange }: ICalendar) => {
	const closePopup = () => setPopup(false)
	const [viewMonth, setViewMonth] = useState(true)
	const [popup, setPopup] = useState(false)

	// let ref = useClickOutside(closePopup)
	let ref = useRef<HTMLDivElement>(null)
	useOutsideClick({
		ref,
		handler: closePopup,
	})
	let { dayName, months } = calendarData
	let date = useRef(new Date(value ? value : new Date().toDateString()))
	let isDateSelected = (day: IDateItem) =>
		value &&
		day.value === new Date(value).getDate() &&
		day.month === "current" &&
		date.current.getMonth() === new Date(value).getMonth()
	const getMonthName = (id) => months.find((month) => month.id === id)!.name
	const [yearState, setYearState] = useState(date.current.getFullYear())
	const [firstYear, setFirstYear] = useState(yearState - (yearState % 10))
	const getDaysInMonth = (m, y) => {
		m += 1
		return /8|3|5|10/.test((--m).toString()) ? 30 : m === 1 ? ((!(y % 4) && y % 100) || !(y % 400) ? 29 : 28) : 31
	}
	const updateCalendarTable = () => {
		var dayofmonth: IDateItem[] = []
		let id = 0
		let previousNumDate = getDaysInMonth(date.current.getMonth() - 1, date.current.getFullYear())
		//get day of week of the first day of the month
		var x = new Date(`${date.current.getFullYear()}-${date.current.getMonth() + 1}-01`).getDay()

		for (var i = 1 - ((x + 6) % 7); i <= getDaysInMonth(date.current.getMonth(), date.current.getFullYear()); i++) {
			if (i <= 0) {
				dayofmonth.push({ id: id, month: "previous", value: previousNumDate + i })
			} else {
				dayofmonth.push({ id: id, month: "current", value: i })
			}
			id++
		}
		for (var j = 0; j < dayofmonth.length % 7; j++) {
			dayofmonth.push({ id: id, month: "next", value: j + 1 })
			id++
		}
		return dayofmonth
	}
	// calendar table
	const [calendar, setCalendar] = useState(updateCalendarTable())

	const swipe = (type) => {
		date.current.setMonth(date.current.getMonth() + (type === "next" ? 1 : -1), 1)
		if ((type === "next" && date.current.getMonth() === 0) || (type === "prev" && date.current.getMonth() === 11))
			setYearState(date.current.getFullYear())
		setCalendar(updateCalendarTable())
	}
	const updateMonth = (month) => {
		date.current.setMonth(month)
		date.current.setFullYear(yearState)
		setCalendar(updateCalendarTable())
	}
	const swipeYear = (type) => {
		date.current.setFullYear(date.current.getFullYear() + (type === "next" ? 1 : -1))
		setYearState(yearState + (type === "next" ? 1 : -1))
		setCalendar(updateCalendarTable())
	}
	const swipe12 = (type) => {
		setFirstYear(firstYear + (type === "next" ? 12 : -12))
	}
	const selectDate = (day) => {
		date.current.setMonth(date.current.getMonth() + (day.month === "previous" ? -1 : day.month === "next" ? 1 : 0))
		date.current.setDate(day.value)
		onChange(date.current.toDateString())
		closePopup()
		setCalendar(updateCalendarTable())
	}
	const displayYear = () => {
		return firstYear + " - " + (firstYear + 11)
	}
	const selectYear = (year) => {
		date.current.setFullYear(year)
		setYearState(year)
		setCalendar(updateCalendarTable())
		setViewMonth(true)
	}
	const render12Year = () => {
		let arr: JSX.Element[] = []
		for (let i = firstYear; i < firstYear + 12; i++) {
			arr.push(
				<Grid
					placeItems="center"
					px={1}
					py={3}
					rounded="full"
					flexBasis="18%"
					flexGrow={1}
					_hover={{ bg: "gray.100" }}
					bg={i === yearState ? "gray.100" : "transparent"}
					color={i === yearState ? "fill.light" : "inherit"}
					fontWeight={i === yearState ? "semibold" : "normal"}
					key={i}
					onClick={() => selectYear(i)}
				>
					{i}
				</Grid>
			)
		}
		return arr
	}
	const clickIcon = () => {
		setPopup(!popup)
	}

	return {
		viewMonth,
		ref,
		popup,
		clickIcon,
		date,
		getMonthName,
		swipe,
		swipe12,
		swipeYear,
		dayName,
		calendar,
		selectDate,
		setViewMonth,
		setFirstYear,
		yearState,
		months,
		updateMonth,
		displayYear,
		render12Year,
		isDateSelected,
	}
}

const Displayer = ({ title, onPrevClick, onNextClick, children, onTitleClick }) => {
	return (
		<Box>
			<Flex px={2} align="center" userSelect="none" w="full" mb={2}>
				<Text fontWeight="semibold" onClick={onTitleClick}>
					{title}
				</Text>
				<Flex align="center" ml="auto">
					<IconButton
						icon={<BsChevronLeft />}
						size="sm"
						rounded="full"
						variant="ghost"
						aria-label="prev"
						onClick={onPrevClick}
					/>
					<IconButton
						icon={<BsChevronRight />}
						size="sm"
						rounded="full"
						variant="ghost"
						aria-label="next"
						onClick={onNextClick}
					/>
				</Flex>
			</Flex>
			{children}
		</Box>
	)
}

export const Calendar = ({ value, onChange }: ICalendar) => {
	const {
		viewMonth,
		ref,
		popup,
		clickIcon,
		date,
		getMonthName,
		swipe,
		swipe12,
		swipeYear,
		dayName,
		calendar,
		selectDate,
		setViewMonth,
		setFirstYear,
		yearState,
		updateMonth,
		months,
		displayYear,
		render12Year,
		isDateSelected,
	} = useCalendar({ value, onChange })
	return (
		<Box pos="relative" ref={ref}>
			<Input
				type="text"
				value={
					value
						? new Date(value).getDate().toLocaleString(undefined, { minimumIntegerDigits: 2 }) +
						  " / " +
						  (new Date(value).getMonth() + 1).toLocaleString(undefined, { minimumIntegerDigits: 2 }) +
						  " / " +
						  new Date(value).getFullYear()
						: ""
				}
				readOnly
			/>
			<Box pos="absolute" right={0} top={0} h="full">
				<IconButton
					icon={<BsCalendar />}
					variant="ghost"
					onClick={clickIcon}
					aria-label="open-calendar"
					colorScheme="black"
				/>
			</Box>
			<Box
				pos="absolute"
				overflow="visible"
				// width="100%"
				zIndex={3}
				top={"100%"}
				right={0}
				transform={"translateY(0.5rem);"}
			>
				<Collapse in={popup} animateOpacity unmountOnExit>
					<Flex width="480px" rounded="md" bg="gray.50" zIndex="3" shadow="md">
						<Box flex={7} borderRight="1px" borderColor="gray.200">
							<Flex p={2} align="center" userSelect="none" w="full">
								<Displayer
									title={getMonthName(date.current.getMonth()) + " " + date.current.getFullYear()}
									onPrevClick={() => swipe("prev")}
									onNextClick={() => swipe("next")}
									onTitleClick={() => {}}
								>
									<Flex w="full" justify="space-between" flexWrap="wrap" userSelect="none" p={1}>
										{dayName.map((day) => (
											<Text
												key={day}
												flexGrow={1}
												flexBasis="12%"
												fontWeight="semibold"
												textAlign="center"
											>
												{day}
											</Text>
										))}
									</Flex>
									<Flex w="full" wrap="wrap" userSelect="none" p={1}>
										{calendar.map((day) => (
											<Text
												flexGrow={1}
												p={1}
												key={day.id}
												onClick={() => selectDate(day)}
												flexBasis="13%"
												textAlign="center"
												bg={isDateSelected(day) ? "gray.100" : "transparent"}
												color={
													isDateSelected(day)
														? "fill.light"
														: day.month === "current"
														? "inherit"
														: "blackAlpha.600"
												}
												fontWeight={isDateSelected(day) ? "semibold" : "normal"}
												rounded="full"
												cursor="pointer"
												_hover={{ bg: "gray.100" }}
											>
												{day.value}
											</Text>
										))}
									</Flex>
								</Displayer>
							</Flex>
						</Box>
						<Box flex={6}>
							{viewMonth ? (
								<>
									<Flex p={2} align="center" userSelect="none" w="full">
										<Displayer
											title={yearState}
											onPrevClick={() => swipeYear("prev")}
											onNextClick={() => swipeYear("next")}
											onTitleClick={() => {
												setViewMonth(!viewMonth)
												setFirstYear(yearState - (yearState % 10))
											}}
										>
											<Wrap w="full" justify="space-between" flexWrap="wrap" userSelect="none">
												{months.map((month) => (
													<Grid
														placeItems="center"
														px={2}
														py={3}
														rounded="full"
														flexBasis="20%"
														flexGrow={1}
														_hover={{ bg: "gray.100" }}
														key={month.id}
														bg={
															month.id === date.current.getMonth()
																? "gray.100"
																: "transparent"
														}
														color={
															month.id === date.current.getMonth()
																? "fill.light"
																: "inherit"
														}
														fontWeight={
															month.id === date.current.getMonth() ? "semibold" : "normal"
														}
														onClick={() => updateMonth(month.id)}
													>
														{month.name.slice(0, 3)}
													</Grid>
												))}
											</Wrap>
										</Displayer>
									</Flex>
								</>
							) : (
								<>
									<Flex p={2} align="center" userSelect="none" w="full">
										<Displayer
											title={displayYear()}
											onTitleClick={() => setViewMonth(!viewMonth)}
											onPrevClick={() => swipe12("prev")}
											onNextClick={() => swipe12("next")}
										>
											<Wrap w="full" justify="space-between" flexWrap="wrap" userSelect="none">
												{render12Year()}
											</Wrap>
										</Displayer>
									</Flex>
								</>
							)}
						</Box>
					</Flex>
				</Collapse>
			</Box>
		</Box>
	)
}

export default Calendar
