import { Box, Collapse, Flex, IconButton, Input, InputGroup, InputLeftElement, InputRightElement, Text } from "@chakra-ui/react"
import { useClickOutside } from "@hooks"
import { useState } from "react"
import { BsChevronDown, BsSearch, BsX } from "react-icons/bs"

interface Item {
	id: string | number
	value: string
	[key: string]: any
}

interface SelectProps {
	selections: Item[]
	selected: Item | null
	onChange: (items: Item | null) => void
	keyField?: string
	valueField?: string
	readOnly?: boolean
}

export const Select = ({ selections, selected, onChange, keyField = "id", valueField = "value", readOnly = false }: SelectProps) => {
	const [isOpen, setIsOpen] = useState(false)

	const boxRef = useClickOutside<HTMLDivElement>(() => setIsOpen(false))

	const isItemSelected = (item: Item) => (selected ? selected[keyField] === item[keyField] : false)

	const [search, setSearch] = useState("")

	const handleChange = (item: Item) => {
		onChange(item)
		setIsOpen(false)
	}

	const handleOpen = () => {
		if (readOnly) return
		setIsOpen(!isOpen)
	}

	return (
		<Box pos="relative" ref={boxRef}>
			<Flex
				w="full"
				justify="space-between"
				border="1px"
				borderColor={"border.primary"}
				minH="2.5rem"
				rounded="md"
				px={4}
				align="center"
				cursor="pointer"
				onClick={handleOpen}
			>
				<Flex flex={1}>
					{selected && (
						<Box key={selected[keyField]} rounded="sm" cursor={"pointer"} onClick={() => onChange(null)}>
							<Text>{selected[valueField]}</Text>
						</Box>
					)}
				</Flex>
				{!readOnly && (
					<Box transform="auto" rotate={isOpen ? 180 : 0}>
						<BsChevronDown />
					</Box>
				)}
			</Flex>
			<Box pos="absolute" top="100%" left={0} w="full" transform="translateY(0.5rem)" zIndex="dropdown">
				<Collapse in={isOpen}>
					<Box rounded="md" background={"background.secondary"} overflow="hidden" border="1px" borderColor={"border.primary"}>
						<InputGroup>
							<InputLeftElement>
								<BsSearch />
							</InputLeftElement>
							<Input
								w="full"
								value={search}
								onChange={e => setSearch(e.target.value)}
								rounded={"null"}
								variant="flushed"
								_focus={{ shadow: "none" }}
							/>
							<InputRightElement>
								<IconButton
									variant="ghost"
									aria-label="clear-search"
									icon={<BsX size="1.5rem" />}
									rounded="full"
									size="sm"
									colorScheme={search ? "red" : "gray"}
									onClick={() => setSearch("")}
									_focus={{ shadow: "none" }}
								/>
							</InputRightElement>
						</InputGroup>
						{selections
							.filter(item => `${item.id}${item.value}`.toLowerCase().includes(search.toLowerCase()))
							.map(item => (
								<Box
									key={item[keyField]}
									cursor="pointer"
									onClick={() => handleChange(item)}
									px={4}
									py={2}
									_even={{ backgroundColor: "background.primary" }}
									_notLast={{ borderBottom: "1px", borderColor: "border.primary" }}
									_hover={{ bg: "background.third" }}
									color={isItemSelected(item) ? "fill.primary" : "text.primary"}
								>
									<Text>{item[valueField]}</Text>
								</Box>
							))}
					</Box>
				</Collapse>
			</Box>
		</Box>
	)
}

export default Select
