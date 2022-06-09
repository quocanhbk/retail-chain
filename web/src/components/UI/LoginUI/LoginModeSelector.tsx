import { Flex, Box, Text } from "@chakra-ui/react"
import { BsFillPersonFill } from "react-icons/bs"
import { FaStore } from "react-icons/fa"

const loginMode = [
	{ id: "employee", text: "Nhân viên", icon: <BsFillPersonFill /> },
	{ id: "owner", text: "Chủ cửa hàng", icon: <FaStore /> }
] as const

export type LoginMode = typeof loginMode[number]["id"]

interface LoginModeSelectorProps {
	currentMode: LoginMode
	setCurrentMode: (mode: LoginMode) => void
}

const LoginModeSelector = ({ currentMode, setCurrentMode }: LoginModeSelectorProps) => {
	return (
		<Flex overflow="hidden" mb={8} pos="relative">
			{loginMode.map(mode => (
				<Flex
					key={mode.id}
					flex={1}
					p={2}
					textAlign="center"
					cursor="pointer"
					fontWeight={currentMode === mode.id ? "bold" : "normal"}
					onClick={() => setCurrentMode(mode.id)}
					align="center"
					justify="center"
					border="1px"
					borderColor={currentMode === mode.id ? "border.primary" : "transparent"}
					rounded="full"
					color={currentMode === mode.id ? "fill.primary" : "text.secondary"}
				>
					<Box mr={2}>{mode.icon}</Box>
					<Text>{mode.text}</Text>
				</Flex>
			))}
		</Flex>
	)
}

export default LoginModeSelector
