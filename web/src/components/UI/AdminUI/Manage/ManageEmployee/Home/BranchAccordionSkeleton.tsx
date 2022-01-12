import { Box, Flex, Skeleton, Text } from "@chakra-ui/react"

const BranchAccordionSkeleton = () => {
	return (
		<Skeleton>
			<Box rounded="md" background="white" shadow="base" overflow={"hidden"}>
				<Flex align="center" justify="space-between" px={4} py={2}>
					<Text fontSize={"lg"}>Branch name</Text>
				</Flex>
			</Box>
		</Skeleton>
	)
}

export default BranchAccordionSkeleton
