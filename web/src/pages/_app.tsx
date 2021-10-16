import type { AppProps } from "next/app"
import { Box } from "@chakra-ui/react"
import Head from "next/head"
import Provider from "@components/shared/Provider"
function MyApp({ Component, pageProps }: AppProps) {
	return (
		<Provider>
			<Head>
				<title>Chain Store</title>
			</Head>
			<Box h="100vh" overflow="hidden">
				<Component {...pageProps} />
			</Box>
		</Provider>
	)
}
export default MyApp
