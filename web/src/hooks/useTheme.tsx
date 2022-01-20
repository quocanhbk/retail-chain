import { useColorModeValue } from "@chakra-ui/react"
import { createContext, ReactNode, useContext } from "react"

const useThemeHook = () => {
	const textPrimary = useColorModeValue("#171717", "white")
	const textSecondary = useColorModeValue("gray.600", "gray.500")
	const backgroundPrimary = useColorModeValue("gray.50", "gray.800")
	const backgroundSecondary = useColorModeValue("white", "gray.900")
	const backgroundThird = useColorModeValue("gray.100", "gray.700")
	const fillPrimary = useColorModeValue("telegram.600", "telegram.400")
	const fillDanger = useColorModeValue("red.600", "red.400")
	const fillSuccess = useColorModeValue("green.600", "green.400")
	const fillWarning = useColorModeValue("yellow.600", "yellow.400")
	const borderPrimary = useColorModeValue("gray.200", "whiteAlpha.300")

	return {
		textPrimary,
		textSecondary,
		backgroundPrimary,
		backgroundSecondary,
		fillPrimary,
		fillDanger,
		fillSuccess,
		fillWarning,
		borderPrimary,
		backgroundThird
	}
}

const UseThemeContext = createContext<ReturnType<typeof useThemeHook> | null>(null)

export const UseThemeProvider = ({ children }: { children: ReactNode }) => {
	const theme = useThemeHook()

	return <UseThemeContext.Provider value={theme}>{children}</UseThemeContext.Provider>
}

export const useTheme = () => {
	const theme = useContext(UseThemeContext)
	if (theme === null) throw new Error("useTheme must be used within a UseThemeProvider")
	return theme
}
