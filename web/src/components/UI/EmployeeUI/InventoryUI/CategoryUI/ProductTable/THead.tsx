import { Box, Flex , Text} from "@chakra-ui/react"

const THead = ()=>{
    return (
        <Flex
				align="center"
				w="full"
                justifyContent={"space-between"}
				px={4}
				py={2}
			>
				<Text w="15rem" isTruncated mr={2} flexShrink={0}>
					{"Mã hàng"}
				</Text>
				<Text fontSize={"sm"} color={"gray.600"} w="18rem" isTruncated flexShrink={0} mr={2}>
					{"Tên hàng"}
				</Text>
				<Text fontSize={"sm"} color={"gray.600"} w="10rem" isTruncated flexShrink={0}>
					{"Giá bán"}
				</Text>
                <Text fontSize={"sm"} color={"gray.600"} w="10rem" isTruncated flexShrink={0}>
					{"Giá vốn"}
				</Text>
                <Text fontSize={"sm"} color={"gray.600"} w="10rem" isTruncated flexShrink={0}>
					{"Tồn kho"}
				</Text>
                <Box w="18px"></Box>
			</Flex>
    )
}
export default THead