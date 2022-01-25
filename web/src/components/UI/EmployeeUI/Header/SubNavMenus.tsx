import { Box, Flex, HStack, Text,useDisclosure,
    MenuItem,
    Menu,
    MenuButton,
    MenuList,
	Button, } from "@chakra-ui/react"
import Link from "next/link"
import { baseNavMenus} from "@constants"
import { useRouter } from "next/router"

interface SubNavMenusProps {
	menu: { id: string; text: string; path: string }[]
}

const SubNavMenus = () => {
	const router = useRouter()

	const currentIndex = baseNavMenus.findIndex(item => item.subMenus.findIndex( item => router.pathname.indexOf(item.path) > -1) > -1)

	return (
		<Flex w="full" justify="center" background={"telegram.600"}>
			<HStack justify={"center"} spacing={0} pos="relative">
				{baseNavMenus.map(item => (
					<Menu>
						<MenuButton 
							fontWeight={500}
							w="8rem" 
							textAlign={"center"} 
							p={2} 
							background={"telegram.600"} 
							color={"white"}  
							_focus={{ boxShadow: 'none' }}
							_hover={{ bg: 'blue.400' }}
						>
							{item.text}
						</MenuButton>
						<MenuList>
							{item.subMenus.map(sub => (
								<Link href={sub.path}><MenuItem>{sub.text}</MenuItem></Link>
							))}
						</MenuList>
					</Menu>
				))}
				<Box
					pos="absolute"
					w="8rem"
					h="5px"
					bg="telegram.300"
					bottom={0}
					left={currentIndex * 8 + "rem"}
					transition="all 0.25s ease-in-out"
				/>
			</HStack>
		</Flex>
	)
}

export default SubNavMenus