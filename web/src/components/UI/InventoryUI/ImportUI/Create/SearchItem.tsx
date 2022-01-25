import { Item } from "@api"
import { Flex, Img, Box, Text } from "@chakra-ui/react"
import { useTheme } from "@hooks"

const SearchItem = ({ item, onClick }: { item: Item; onClick?: () => void }) => {
	const theme = useTheme()
	return (
		<Flex onClick={onClick} cursor="pointer">
			<Img src={item.image} boxSize={"2.5rem"} rounded="md" />
			<Box ml={4}>
				<Text>{item.name}</Text>
				<Text fontSize={"sm"} color={theme.textSecondary}>
					{"Barcode: "}
					{item.barcode}
				</Text>
			</Box>
		</Flex>
	)
}

export default SearchItem
