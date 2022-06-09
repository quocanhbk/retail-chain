import { Item } from "@api"
import { Flex, Img, Box, Text } from "@chakra-ui/react"

const SearchItem = ({ item, onClick }: { item: Item; onClick?: () => void }) => {
	return (
		<Flex onClick={onClick} cursor="pointer">
			<Img src={item.image} boxSize={"2.5rem"} rounded="md" />
			<Box ml={4}>
				<Text>{item.name}</Text>
				<Text fontSize={"sm"} color={"text.secondary"}>
					{"Barcode: "}
					{item.barcode}
				</Text>
			</Box>
		</Flex>
	)
}

export default SearchItem
