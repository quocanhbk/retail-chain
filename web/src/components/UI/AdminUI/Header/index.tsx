import { Flex, Heading, Avatar, Text, Button, Icon } from "@chakra-ui/react"
import {MdLogout} from "react-icons/md"
import { useStoreState } from "@store"
import { useLogout } from "@hooks"

export const Header = () => {
	const info = useStoreState(s => s.info)
	const { mutate, isLoading } = useLogout()
	return (
		<Flex align="center" w="full" justify="space-between" px={4} py={4} shadow="base">
			<Heading fontSize="xl" fontFamily="Brandon" color="telegram.500" fontWeight={"900"}>
				BKRM ADMIN
			</Heading>
			<Flex>
				<Flex align="center">
					<Avatar size="sm" name={info?.user.name} src={info?.user.avatar_url || undefined} />
					<Text ml={1} mr={5}>{info?.user.name}</Text>
					
					<Button onClick={() => mutate()} isLoading={isLoading} size="sm">
						Logout <Icon as={MdLogout} ml={2}></Icon>
					</Button>
				</Flex>
			</Flex>
		</Flex>
	)
}

export default Header
