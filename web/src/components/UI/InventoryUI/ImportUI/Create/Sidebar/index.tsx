import { Box, Flex, Heading, Text } from "@chakra-ui/react"
import { format } from "date-fns"
import { ReactNode, useState } from "react"

interface SidebarProps {
	children: ReactNode
}

const Sidebar = ({ children }: SidebarProps) => {
	const [time] = useState(new Date())
	return (
		<Box bg={"background.secondary"} p={4} rounded="md">
			<Flex w="full" justify="space-between" mb={4} align="center">
				<Heading fontSize={"xl"}>{"Thông tin phiếu"}</Heading>
				<Text color={"text.secondary"} fontSize="sm">
					{format(time, "HH:mm dd/MM/yyyy")}
				</Text>
			</Flex>
			{children}
		</Box>
	)
}

export default Sidebar
