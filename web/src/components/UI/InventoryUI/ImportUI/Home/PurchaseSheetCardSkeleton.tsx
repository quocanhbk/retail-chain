import { Box, Flex, Text, Skeleton } from "@chakra-ui/react"
import { useTheme } from "@hooks"

const PurchaseSheetCardSkeleton = () => {
	const theme = useTheme()

	return (
		<Box py={1} align="stretch" background={theme.backgroundSecondary} rounded="md" w="15rem" cursor="pointer" _hover={{}}>
			<Box borderBottom={"1px"} borderColor={theme.borderPrimary} py={2} px={4}>
				<Skeleton>
					<Text fontWeight={"bold"} w="full">
						CODE
					</Text>
				</Skeleton>
			</Box>
			<Box py={2} px={4}>
				<Flex align="center" mb={2}>
					<Skeleton>
						<Text>{"Supplier Name"}</Text>
					</Skeleton>
				</Flex>
				<Flex align="center" mb={2}>
					<Skeleton>
						<Text>{"Employee"}</Text>
					</Skeleton>
				</Flex>
				<Flex align="center" mb={2}>
					<Skeleton>
						<Text>{"Date Date Date"}</Text>
					</Skeleton>
				</Flex>
				<Flex align="center" mb={2}>
					<Skeleton>
						<Text>$$$$$$$$$$$</Text>
					</Skeleton>
				</Flex>
			</Box>
		</Box>
	)
}

export default PurchaseSheetCardSkeleton
