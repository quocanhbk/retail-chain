import { Box, Flex, Heading, Text } from "@chakra-ui/react"
import { useTheme } from "@hooks"
import { format } from "date-fns"
import { useState } from "react"
import SupplierSearchInput from "./SupplierSearchInput"

const Sidebar = () => {
	const theme = useTheme()
	const [time] = useState(new Date())
	return (
		<Box bg={theme.backgroundSecondary} p={4} rounded="md" flex={2} flexShrink={0}>
			<Flex w="full" justify="space-between" mb={4}>
				<Heading fontSize={"xl"}>{"Thông tin phiếu"}</Heading>
				<Text color={theme.textSecondary}>{format(time, "HH:mm dd/MM/yyyy")}</Text>
			</Flex>
			<SupplierSearchInput />
		</Box>
	)
}

export default Sidebar
