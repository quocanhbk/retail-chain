import { Grid, Text } from "@chakra-ui/react"

const NotFound = () => {
	return (
		<Grid pos="absolute" top={0} left={0} w="full" h="full" placeItems={"center"}>
			<Text fontSize={"lg"}>{"Không tìm thấy phiếu nhập hàng, vui lòng kiểm tra lại"}</Text>
		</Grid>
	)
}

export default NotFound
