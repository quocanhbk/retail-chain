import { extendTheme } from "@chakra-ui/react"

const theme = extendTheme({
	fonts: {
		heading: "Roboto",
		body: "Roboto",
	},
	components: {
		Heading: {
			baseStyle: {
				fontWeight: 600,
			},
			sizes: {
				small: {
					fontSize: ["lg", "xl"],
				},
				medium: {
					fontSize: ["xl", "2xl"],
				},
				large: {
					fontSize: ["2xl", "3xl"],
				},
			},
			defaultProps: {
				size: "medium",
			},
		},
		Text: {
			sizes: {
				small: {
					fontSize: ["xs", "sm"],
				},
				medium: {
					fontSize: ["sm", "md"],
				},
				large: {
					fontSize: ["md", "lg"],
				},
			},
			defaultProps: {
				size: "medium",
			},
		},
		Button: {
			defaultProps: {
				colorScheme: "telegram",
			},
		},
		colors: {},
	},
	breakpoints: ["0px", "480px", "960px", "1440px", "1920px"],
})

export default theme
