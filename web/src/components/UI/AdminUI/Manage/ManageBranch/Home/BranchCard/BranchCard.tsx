import { Branch } from "@api"
import { Text, Img, Flex } from "@chakra-ui/react"
import { baseURL } from "src/api/fetcher"
import Container from "./Container"
import Link from "next/link"

interface BranchCardProps {
	data: Branch
	index: number
}

const BranchCard = ({ data, index }: BranchCardProps) => {
	const { name, address, image } = data

	return (
		<Link href={`/admin/manage/branch/${data.id}`}>
			<Container custom={index} pos="relative">
				<Flex justify={"center"} h="10rem" w="full" bg="white" flexShrink={0}>
					<Img src={`${baseURL}/branch/image/${image}`} alt="store" h="full" />
				</Flex>
				<Flex flex={1} px={4} direction="column" w="full" py={2} overflow="hidden">
					<Text fontSize={"lg"} fontWeight={"bold"}>
						{name}
					</Text>
					<Text
						color="gray.500"
						w="full"
						sx={{
							display: "-webkit-box",
							WebkitBoxOrient: "vertical",
							WebkitLineClamp: 2,
							overflow: "hidden",
							textOverflow: "ellipsis",
						}}
					>
						{address}
					</Text>
				</Flex>
			</Container>
		</Link>
	)
}

export default BranchCard
