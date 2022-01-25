import { Box, Flex, ScaleFade, Text, useColorMode, useOutsideClick } from "@chakra-ui/react"
import { useTheme } from "@hooks"
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
	const { fillPrimary, backgroundSecondary, fillDanger, borderPrimary } = useTheme()
	return (
		<Flex align="center" px={4} py={1} bg={fillPrimary} rounded="md" pos="relative" zIndex={"dropdown"}>
			<Text fontWeight={"bold"} mr={4} fontSize={"lg"} color="white">
				{name}
			</Text>
			<Box rounded="full" cursor={"pointer"} onClick={() => setIsOpen(isOpen => !isOpen)} ref={boxRef}>
				<Box color="white">
					<BsThreeDots size="1.2rem" />
				</Box>
			</Box>
			<Box pos="absolute" top="100%" right={0} transform={"translateY(0.5rem)"}>
				<ScaleFade in={isOpen} unmountOnExit>
					<Box background={backgroundSecondary} shadow="base" rounded="md" w="10rem" p={2} border="1px" borderColor={borderPrimary}>
						<Flex align="center" w="full" cursor="pointer" onClick={() => toggleColorMode()} px={2} py={1}>
							{colorMode === "light" ? <BsFillSunFill /> : <BsFillMoonFill />}
							<Text ml={2}>{colorMode === "light" ? "Theme sáng" : "Theme tối"}</Text>
						</Flex>
						<Flex align="center" w="full" cursor="pointer" onClick={onLogout} px={2} py={1} color={fillDanger}>
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
