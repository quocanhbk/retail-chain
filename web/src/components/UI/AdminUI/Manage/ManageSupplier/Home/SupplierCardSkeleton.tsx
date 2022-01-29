import { Box, Flex, Skeleton, Text } from "@chakra-ui/react"
import { useTheme } from "@hooks"

const SupplierCardSkeleton = () => {
	const theme = useTheme()

	return (
		<Box rounded="md" backgroundColor={theme.backgroundSecondary}>
			<Flex align="center" borderBottom={"1px"} borderColor={theme.borderPrimary} px={4} py={2}>
				<Skeleton>
					<Text fontWeight={"bold"} fontSize={"lg"}>
						Supplier name
					</Text>
				</Skeleton>
			</Flex>
			<Box p={4}>
				<Flex align="center" w="full" mb={2}>
					<Skeleton>
						<Text flex={1} isTruncated>
							Supplier phone
						</Text>
					</Skeleton>
				</Flex>
				<Flex align="center" w="full">
					<Skeleton>
						<Text flex={1} isTruncated>
							Supplier email
						</Text>
					</Skeleton>
				</Flex>
			</Box>
		</Box>
	)
}

export default SupplierCardSkeleton
