import { Branch } from "@api"
import { Text, Img, Flex, VStack } from "@chakra-ui/react"
import { baseURL } from "src/api/fetcher"
import Container from "./Container"

interface BranchCardProps {
	data: Branch
	index: number
}

const BranchCard = ({ data, index }: BranchCardProps) => {
	const { name, address, image } = data

	return (
		<Container custom={index}>
			<Flex justify={"center"} h="10rem" w="full" bg="white" cg>
				<Img src={`${baseURL}/branch/image/${image}`} alt="store" h="full" />
			</Flex>
			<VStack flex={1} direction="column" align="center" justify="center" spacing={1}>
				<Text fontSize={"xl"} fontWeight={"bold"}>
					{name}
				</Text>
				<Text>{address}</Text>
			</VStack>
		</Container>
	)
}

export default BranchCard
