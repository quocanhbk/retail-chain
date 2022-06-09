import { Box, chakra, Flex, ScaleFade, Text, VStack } from "@chakra-ui/react"
import { useClickOutside } from "@hooks"
import { SortField } from "@constants"
import { BsFillTriangleFill } from "react-icons/bs"
import { useState } from "react"

interface SorterProps {
	currentSort: SortField
	data: SortField[]
	onChange: (sort: SortField) => void
}

export const Sorter = ({ currentSort, onChange, data }: SorterProps) => {
	const [isOpen, setIsOpen] = useState(false)
	const ref = useClickOutside<HTMLDivElement>(() => setIsOpen(false))
	return (
		<Flex
			w="20rem"
			border="1px"
			borderColor={"border.primary"}
			backgroundColor={"background.secondary"}
			h="2.5rem"
			px={4}
			rounded="md"
			ml={4}
			align="center"
			pos="relative"
			onClick={() => setIsOpen(!isOpen)}
		>
			<Flex w="full" align="center" justify="space-between" cursor={"pointer"} ref={ref}>
				<Text>
					<chakra.span>
						{data.find(sortField => sortField.key === currentSort.key && sortField.order === currentSort.order)?.text}
					</chakra.span>
				</Text>
				<Box transform="auto" rotate={isOpen ? 0 : 180}>
					<BsFillTriangleFill size="0.5rem" />
				</Box>
			</Flex>
			<Box pos="absolute" left={0} top="100%" transform="translateY(0.5rem)" w="full" zIndex={10}>
				<ScaleFade in={isOpen}>
					<Box border="1px" borderColor={"border.primary"} w="full" backgroundColor={"background.secondary"} rounded="md">
						<VStack align="stretch" spacing={0}>
							{data.map(sortField => (
								<Text
									key={`${sortField.key}-${sortField.order}`}
									onClick={() => onChange(sortField)}
									cursor="pointer"
									px={4}
									py={2}
									color={
										sortField.key === currentSort.key && sortField.order === currentSort.order
											? "fill.primary"
											: "text.secondary"
									}
									_hover={{ backgroundColor: "background.third" }}
								>
									{sortField.text}
								</Text>
							))}
						</VStack>
					</Box>
				</ScaleFade>
			</Box>
		</Flex>
	)
}
