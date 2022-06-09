import { extendTheme } from "@chakra-ui/react"

const theme = extendTheme({
	config: {
		initialColorMode: "light",
		useSystemColorMode: false
	},
	fonts: {
		heading: "Roboto",
		body: "Roboto"
	},
	semanticTokens: {
		colors: {
			"text.primary": {
				default: "blackAlpha.900",
				_dark: "whiteAlpha.900"
			},
			"text.secondary": {
				default: "gray.600",
				_dark: "gray.500"
			},
			"background.primary": {
				default: "gray.50",
				_dark: "gray.800"
			},
			"background.secondary": {
				default: "white",
				_dark: "gray.900"
			},
			"background.third": {
				default: "gray.100",
				_dark: "gray.700"
			},
			"background.fade": {
				default: "blackAlpha.50",
				_dark: "whiteAlpha.50"
			},
			"fill.primary": {
				default: "telegram.600",
				_dark: "telegram.400"
			},
			"fill.danger": {
				default: "red.600",
				_dark: "red.400"
			},
			"fill.success": {
				default: "green.600",
				_dark: "green.400"
			},
			"fill.warning": {
				default: "yellow.600",
				_dark: "yellow.400"
			},
			"border.primary": {
				default: "gray.200",
				_dark: "whiteAlpha.300"
			}
		}
	},
	styles: {
		global: props => ({
			html: {
				fontSize: ["14px", "15px", "15px", "15px", "16px"]
			},
			body: {
				bg: props.colorMode === "light" ? "background.primary.light" : "background.primary.dark",
				color: props.colorMode === "light" ? "text.primary.light" : "text.primary.dark"
			}
		})
	},
	components: {
		// color="telegram.500" size="sm" thickness="3px"
		Spinner: {
			defaultProps: {
				size: "xs",
				thickness: "5px"
			},
			baseStyle: {
				color: "telegram.600"
			}
		},
		Input: {
			variants: {
				outline: props => ({
					bg: props.colorMode === "light" ? "red" : "blue"
				})
			}
		}
	},
	colors: {
		background: {
			primary: {
				light: "#F7FAFC",
				dark: "#171923"
			},
			secondary: {
				light: "#FFFFFF",
				dark: "#1A202C"
			}
		},
		text: {
			primary: {
				light: "#171717",
				dark: "#E4E6EB"
			},
			secondary: {
				light: "#4A5568",
				dark: "#E2E8F0"
			}
		}
	},
	breakpoints: ["0px", "480px", "960px", "1440px", "1920px"]
})

export default theme
