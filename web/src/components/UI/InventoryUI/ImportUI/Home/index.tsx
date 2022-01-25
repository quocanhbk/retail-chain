import { Box, Button, Flex, Heading } from "@chakra-ui/react"
import Link from "next/link"

const ImportHomeUI = () => {
	return (
		<Box p={4}>
			<Flex w="full" align="center" justify="space-between">
				<Heading mb={4} fontSize={"2xl"}>
					Quản lý chi nhánh
				</Heading>
				<Link href="/main/inventory/import/create">
					<Button size="sm" variant="ghost">
						{"Nhập hàng"}
					</Button>
				</Link>
			</Flex>
		</Box>
	)
}

export default ImportHomeUI
