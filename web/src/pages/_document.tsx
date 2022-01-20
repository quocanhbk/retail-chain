// @next/next/no-document-import-in-page
import { ColorModeScript } from "@chakra-ui/react"
import Document, { Html, Head, Main, NextScript } from "next/document"
import theme from "src/theme"

class MyDocument extends Document {
	render() {
		return (
			<Html lang="en">
				<Head>
					<link rel="preconnect" href="https://fonts.googleapis.com" />
					<link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="true" />

					<link
						href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Roboto:wght@400;500;700;900&display=swap"
						rel="stylesheet"
					></link>
				</Head>
				<body>
					<ColorModeScript initialColorMode={theme.config.initialColorMode} />
					<Main />
					<NextScript />
				</body>
			</Html>
		)
	}
}

export default MyDocument
