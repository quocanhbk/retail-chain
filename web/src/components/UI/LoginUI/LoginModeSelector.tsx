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
		<Flex mb={8} boxShadow={"md"} rounded="full" bg="background.primary">
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
					boxShadow={currentMode === mode.id ? "lg" : "none"}
					rounded="full"
					color={currentMode === mode.id ? "fill.primary" : "text.secondary"}
					backgroundColor={currentMode === mode.id ? "background.secondary" : "transparent"}
				>
					<Box mr={2}>{mode.icon}</Box>
					<Text>{mode.text}</Text>
				</Flex>
			))}
		</Flex>
	)
}

export default LoginModeSelector
