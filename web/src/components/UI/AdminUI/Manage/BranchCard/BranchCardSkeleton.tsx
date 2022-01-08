import { Text, Flex, Skeleton, VStack } from "@chakra-ui/react"
import Container from "./Container"

const BranchCardSkeleton = () => {
	return (
		<Container custom={0}>
			<Flex justify={"center"} h="10rem" w="full" bg="white">
				<Skeleton h="full" w="full" />
			</Flex>
			<VStack flex={1} direction="column" align="center" justify="center" spacing={1}>
				<Skeleton>
					<Text fontSize={"xl"} fontWeight={"bold"}>
						Branch Name
					</Text>
				</Skeleton>
				<Skeleton>
					<Text>Branch Address</Text>
				</Skeleton>
			</VStack>
		</Container>
	)
}

export default BranchCardSkeleton
