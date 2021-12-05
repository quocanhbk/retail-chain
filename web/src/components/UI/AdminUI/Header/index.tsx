import { Flex, Heading, Avatar, Text } from "@chakra-ui/react"
import { useStoreState } from "@store"

export const Header = () => {
	const info = useStoreState(s => s.info)

	return (
		<Flex align="center" w="full" justify="space-between" px={4} py={4} shadow="base">
			<Heading fontSize="xl" fontFamily="Brandon" color="telegram.500" fontWeight={"900"}>
				BKRM ADMIN
			</Heading>
			<Flex>
				<Flex align="center">
					<Avatar size="sm" name={info?.user.name} src={info?.user.avatar_url || undefined} />
					<Text ml={2}>{info?.user.name}</Text>
				</Flex>
			</Flex>
		</Flex>
	)
}

export default Header
