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
		Button: {
			defaultProps: {
				colorScheme: "telegram"
			}
		},
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
