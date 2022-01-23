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

	// const currentIndex = menu.findIndex(item => router.pathname.indexOf(item.path) > -1)

	// if (menu.length === 0) return null
	const { isOpen, onOpen, onClose } = useDisclosure()
	return (
		<Flex w="full" justify="center" background={"telegram.600"}>
			<HStack justify={"center"} spacing={0} pos="relative">
				{baseNavMenus.map(item => (
					<Menu>
						<MenuButton as={Button} >
							{item.id}
						</MenuButton>
						<MenuList>
							{item.subMenus.map(sub => (
								<MenuItem>{sub.text}</MenuItem>
							))}
						</MenuList>
					</Menu>
				))}
				{/* <Box
					pos="absolute"
					w="8rem"
					h="5px"
					bg="telegram.300"
					bottom={0}
					// left={currentIndex * 8 + "rem"}
					transition="all 0.25s ease-in-out"
				/> */}
			</HStack>
		</Flex>
	)
}

export default SubNavMenus