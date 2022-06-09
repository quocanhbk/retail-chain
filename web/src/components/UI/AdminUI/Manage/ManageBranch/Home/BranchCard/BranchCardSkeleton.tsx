import { Text, Flex, Skeleton } from "@chakra-ui/react"
import Container from "./Container"

const BranchCardSkeleton = () => {
	return (
		<Container custom={0}>
			<Flex justify={"center"} h="10rem" w="full" bg="white">
				<Skeleton h="full" w="full" />
			</Flex>

			<Flex flex={1} px={4} direction="column" w="full" py={2} overflow="hidden">
				<Skeleton>
					<Text fontSize={"lg"} fontWeight={"bold"}>
						Branch Name
					</Text>
				</Skeleton>
				<Skeleton>
					<Text>Branch Address</Text>
				</Skeleton>
			</Flex>
		</Container>
	)
}

export default BranchCardSkeleton
