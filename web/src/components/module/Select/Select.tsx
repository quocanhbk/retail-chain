import {
	Box,
	Collapse,
	Flex,
	IconButton,
	Input,
	InputGroup,
	InputLeftElement,
	InputRightElement,
	Text,
	useOutsideClick,
	Wrap,
} from "@chakra-ui/react"
import { Motion, SearchInput } from "@components/shared"
import { AnimatePresence } from "framer-motion"
import { useRef, useState } from "react"
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
}

export const Select = ({ selections, selected, onChange, keyField = "id", valueField = "value" }: SelectProps) => {
	const [isOpen, setIsOpen] = useState(false)

	const boxRef = useRef<HTMLDivElement>(null)
	useOutsideClick({
		ref: boxRef,
		handler: () => setIsOpen(false),
	})

	const isItemSelected = (item: Item) => (selected ? selected[keyField] === item[keyField] : false)

	const [search, setSearch] = useState("")

	const handleChange = (item: Item) => {
		onChange(item)
		setIsOpen(false)
	}

	return (
		<Box pos="relative" ref={boxRef}>
			<Flex
				w="full"
				justify="space-between"
				bg="white"
				border="1px"
				borderColor={"gray.200"}
				minH="2.5rem"
				rounded="md"
				px={4}
				align="center"
				cursor="pointer"
				onClick={() => setIsOpen(!isOpen)}
			>
				<Flex flex={1}>
					<AnimatePresence exitBeforeEnter>
						{selected && (
							<Motion.Box
								key={selected[keyField]}
								rounded="sm"
								initial={{ opacity: 0 }}
								animate={{ opacity: 1 }}
								exit={{ opacity: 0 }}
								transition={{ duration: 0.25, type: "tween" }}
								cursor={"pointer"}
								onClick={() => onChange(null)}
							>
								<Text>{selected[valueField]}</Text>
							</Motion.Box>
						)}
					</AnimatePresence>
				</Flex>
				<Box transform="auto" rotate={isOpen ? 180 : 0}>
					<BsChevronDown />
				</Box>
			</Flex>
			<Box pos="absolute" top="100%" left={0} w="full" transform="translateY(0.5rem)" zIndex="dropdown">
				<Collapse in={isOpen}>
					<Box border="1px" borderColor="gray.200" rounded="md" background="white" overflow="hidden">
						<InputGroup>
							<InputLeftElement>
								<BsSearch />
							</InputLeftElement>
							<Input
								w="full"
								background="white"
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
									py={1}
									_even={{ backgroundColor: "gray.50" }}
									_notLast={{ borderBottom: "1px", borderColor: "gray.200" }}
									_hover={{ bg: "gray.100" }}
									color={isItemSelected(item) ? "telegram.600" : "blackAlpha.700"}
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
