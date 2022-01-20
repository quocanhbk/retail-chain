import { Box, chakra, Collapse, Flex, Text, VStack } from "@chakra-ui/react"
import { useClickOutside, useTheme } from "@hooks"
import { branchSorts } from "@constants"
import { BsFillTriangleFill } from "react-icons/bs"
import { useState } from "react"

interface SorterProps {
	currentSort: { key: string; order: string }
	onChange: (sort: { key: string; order: string }) => void
}

const Sorter = ({ currentSort, onChange }: SorterProps) => {
	const { borderPrimary, backgroundSecondary, textSecondary, fillPrimary, backgroundThird } = useTheme()
	const [isOpen, setIsOpen] = useState(false)
	const ref = useClickOutside<HTMLDivElement>(() => setIsOpen(false))
	return (
		<Flex
			w="20rem"
			border="1px"
			borderColor={borderPrimary}
			backgroundColor={backgroundSecondary}
			h="2.5rem"
			px={4}
			rounded="md"
			ml={4}
			align="center"
			pos="relative"
		>
			<Flex
				w="full"
				align="center"
				justify="space-between"
				cursor={"pointer"}
				onClick={() => setIsOpen(!isOpen)}
				ref={ref}
			>
				<Text>
					<chakra.span>
						{branchSorts.find(b => b.key === currentSort.key && b.order === currentSort.order)?.text}
					</chakra.span>
				</Text>
				<Box transform="auto" rotate={isOpen ? 0 : 180}>
					<BsFillTriangleFill size="0.5rem" />
				</Box>
			</Flex>
			<Box pos="absolute" left={0} top="100%" transform="translateY(0.5rem)" w="full">
				<Collapse in={isOpen}>
					<Box
						border="1px"
						borderColor={borderPrimary}
						w="full"
						backgroundColor={backgroundSecondary}
						rounded="md"
						py={2}
						px={2}
					>
						<VStack align="stretch">
							{branchSorts.map(b => (
								<Text
									key={`${b.key}-${b.order}`}
									onClick={() => onChange({ key: b.key, order: b.order })}
									cursor="pointer"
									px={2}
									py={1}
									color={
										b.key === currentSort.key && b.order === currentSort.order
											? fillPrimary
											: textSecondary
									}
									_hover={{ backgroundColor: backgroundThird }}
								>
									{b.text}
								</Text>
							))}
						</VStack>
					</Box>
				</Collapse>
			</Box>
		</Flex>
	)
}

export default Sorter
