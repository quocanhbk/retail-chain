import { Branch, getBranchImage } from "@api"
import { Text, Img, Flex } from "@chakra-ui/react"
import Container from "./Container"
import Link from "next/link"
import { useTheme } from "@hooks"

interface BranchCardProps {
	data: Branch
	index: number
}

const BranchCard = ({ data, index }: BranchCardProps) => {
	const { name, address } = data
	const { textSecondary } = useTheme()
	return (
		<Link href={`/admin/manage/branch/${data.id}`}>
			<Container custom={index} pos="relative">
				<Flex justify={"center"} h="10rem" w="full" bg="white" flexShrink={0}>
					<Img src={getBranchImage(data.image_key)} alt="store" h="full" />
				</Flex>
				<Flex flex={1} px={4} direction="column" w="full" py={2} overflow="hidden">
					<Text fontSize={"lg"} fontWeight={"bold"}>
						{name}
					</Text>
					<Text
						color={textSecondary}
						w="full"
						sx={{
							display: "-webkit-box",
							WebkitBoxOrient: "vertical",
							WebkitLineClamp: 2,
							overflow: "hidden",
							textOverflow: "ellipsis"
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
