import { Box, Flex, Skeleton, Text } from "@chakra-ui/react"

const SupplierCardSkeleton = () => {
	return (
		<Box rounded="md" backgroundColor={"background.secondary"}>
			<Flex align="center" borderBottom={"1px"} borderColor={"border.primary"} px={4} py={2}>
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
