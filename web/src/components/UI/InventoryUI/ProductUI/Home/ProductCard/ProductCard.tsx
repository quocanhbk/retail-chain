import { Text, Img, Flex } from "@chakra-ui/react"
import Container from "./Container"
import Link from "next/link"
import { useTheme } from "@hooks"


const ProductCard = ({ data, index }) => {
	const { textSecondary } = useTheme()
	return (
		// <Link href={`/main/inventory/product/${data.id}`}>
			<Container custom={index} pos="relative">
				<Flex justify={"center"} h="6rem" w="full" bg="white" flexShrink={0}>
					<Img src={data.image} alt="store" h="full" />
				</Flex>
				<Flex flex={1} px={4} direction="column" w="full" py={2} overflow="hidden">
					<Text fontSize={"lg"} fontWeight={"bold"} lineHeight={1.1}>
						{data.name}
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
						{data.barcode}
					</Text>
				</Flex>
			</Container>
		// </Link>
	)
}

export default ProductCard
