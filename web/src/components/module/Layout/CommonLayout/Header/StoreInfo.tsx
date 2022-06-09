import { Box, Flex, ScaleFade, Text, useColorMode, useOutsideClick } from "@chakra-ui/react"
import { useRef, useState } from "react"
import { BsFillMoonFill, BsFillSunFill, BsPower, BsThreeDots } from "react-icons/bs"

interface InfoProps {
	name: string
	onLogout: () => void
}
const StoreInfo = ({ name, onLogout }: InfoProps) => {
	const [isOpen, setIsOpen] = useState(false)
	const boxRef = useRef<HTMLDivElement>(null)
	useOutsideClick({
		ref: boxRef,
		handler: () => setIsOpen(false)
	})

	const { colorMode, toggleColorMode } = useColorMode()
	return (
		<Flex align="center" px={4} py={1} rounded="md" pos="relative" zIndex={"dropdown"} border="1px" borderColor={"border.primary"}>
			<Text fontWeight={"bold"} mr={4} fontSize={"lg"} color="white">
				{name}
			</Text>
			<Box
				rounded="full"
				cursor={"pointer"}
				onClick={() => setIsOpen(isOpen => !isOpen)}
				border="1px"
				p={1}
				borderColor="border.primary"
				ref={boxRef}
			>
				<Box color="white">
					<BsThreeDots size="1.2rem" />
				</Box>
			</Box>
			<Box pos="absolute" top="100%" right={0} transform={"translateY(0.5rem)"}>
				<ScaleFade in={isOpen} unmountOnExit>
					<Box
						background={"background.secondary"}
						shadow="base"
						rounded="md"
						w="10rem"
						p={2}
						border="1px"
						borderColor={"border.primary"}
					>
						<Flex align="center" w="full" cursor="pointer" onClick={() => toggleColorMode()} px={2} py={1}>
							{colorMode === "light" ? <BsFillSunFill /> : <BsFillMoonFill />}
							<Text ml={2}>{colorMode === "light" ? "Theme sáng" : "Theme tối"}</Text>
						</Flex>
						<Flex align="center" w="full" cursor="pointer" onClick={onLogout} px={2} py={1} color={"fill.danger"}>
							<BsPower />
							<Text ml={2}>Đăng xuất</Text>
						</Flex>
					</Box>
				</ScaleFade>
			</Box>
		</Flex>
	)
}

export default StoreInfo
