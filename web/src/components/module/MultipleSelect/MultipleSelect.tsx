import { Box, Collapse, Flex, Text, useOutsideClick, Wrap } from "@chakra-ui/react"
import { Motion } from "@components/shared"
import { AnimatePresence } from "framer-motion"
import { useRef, useState } from "react"
import { BsChevronDown } from "react-icons/bs"

interface Item {
	id: string
	value: string
	[key: string]: any
}

interface MultipleSelectProps {
	selections: Item[]
	selected: Item[]
	onChange: (items: Item[]) => void
	keyField?: string
	valueField?: string
}

export const MultipleSelect = ({
	selections,
	selected,
	onChange,
	keyField = "id",
	valueField = "value",
}: MultipleSelectProps) => {
	const [isOpen, setIsOpen] = useState(false)

	const handleChange = (item: Item) => {
		const newSelected = selected.includes(item)
			? selected.filter(selectedItem => selectedItem[keyField] !== item[keyField])
			: [...selected, item]
		onChange(newSelected)
	}

	const handleClearItem = (item: Item) => {
		onChange(selected.filter(selectedItem => selectedItem[keyField] !== item[keyField]))
	}

	const boxRef = useRef<HTMLDivElement>(null)
	useOutsideClick({
		ref: boxRef,
		handler: () => setIsOpen(false),
	})

	const isItemSelected = (item: Item) => selected.find(selectedItem => selectedItem[keyField] === item[keyField])

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
				<Wrap>
					<AnimatePresence>
						{selected.map(item => (
							<Motion.Box
								key={item[keyField]}
								bg="telegram.100"
								px={2}
								py={0.5}
								rounded="sm"
								initial={{ opacity: 0 }}
								animate={{ opacity: 1 }}
								exit={{ opacity: 0 }}
								transition={{ duration: 0.25, type: "tween" }}
								cursor={"pointer"}
								onClick={() => handleClearItem(item)}
							>
								<Text fontSize={"sm"}>{item[valueField]}</Text>
							</Motion.Box>
						))}
					</AnimatePresence>
				</Wrap>
				<Box transform="auto" rotate={isOpen ? 180 : 0}>
					<BsChevronDown />
				</Box>
			</Flex>
			<Box pos="absolute" top="100%" left={0} w="full" transform="translateY(0.5rem)" zIndex="dropdown">
				<Collapse in={isOpen}>
					<Box border="1px" borderColor="gray.200" rounded="md" background="white">
						{selections.map(item => (
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

export default MultipleSelect
