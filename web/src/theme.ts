import { extendTheme } from "@chakra-ui/react"

const theme = extendTheme({
	fonts: {
		heading: "Roboto",
		body: "Roboto",
	},
	globals: {
		html: {
			fontSize: ["14px", "15px", "15px", "16px", "16px"],
		},
	},
	components: {
		Button: {
			defaultProps: {
				colorScheme: "telegram",
				size: "sm",
			},
		},
		// color="telegram.500" size="sm" thickness="3px"
		Spinner: {
			defaultProps: {
				size: "xs",
				thickness: "5px",
			},
			baseStyle: {
				color: "telegram.600",
			},
		},
	},
	breakpoints: ["0px", "480px", "960px", "1440px", "1920px"],
})

export default theme
