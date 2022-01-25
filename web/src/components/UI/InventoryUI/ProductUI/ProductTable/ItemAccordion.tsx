import { AccordionButton, AccordionIcon, AccordionItem, AccordionPanel, Box, Button, Flex , Link, Text} from "@chakra-ui/react"

const ItemAccordion = () =>{
    return (
		<AccordionItem border="none" pb={2}>
					<Flex
						align="center"
						w="full"
                        textAlign={"start"}
						cursor="pointer"
						bg={"white"}
						_hover={{ bg: "gray.200" }}
					>
                        <AccordionButton  _expanded={{ bg: "#e1deff" }} px={4} py={2} textAlign={"start"} justifyContent={"space-between"}>
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
                        <AccordionIcon justifyContent="right" /></AccordionButton>
					</Flex>			
			<AccordionPanel pb={2} border={"1px"} borderColor={"gray.200"}>
                <Text>Thong tin chi tiet</Text>
				{/* <Button size="sm" colorScheme="blue">
					{"Chi tiết"}
				</Button> */}
			</AccordionPanel>
		</AccordionItem>
	)
}

export default ItemAccordion