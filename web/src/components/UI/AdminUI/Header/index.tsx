import { Flex, Heading, HStack } from "@chakra-ui/react"
import NavMenus from "./NavMenus"
import StoreInfo from "./StoreInfo"

export const Header = () => {
	return (
		<Flex align="center" w="full" justify="space-between" px={4} shadow="base" py={2}>
			<Heading fontSize="xl" color="gray.500" fontWeight={"900"}>
				BKRM ADMIN
			</Heading>
			<HStack align="center" spacing={8}>
				<NavMenus />
				<StoreInfo />
			</HStack>
		</Flex>
	)
}

export default Header
