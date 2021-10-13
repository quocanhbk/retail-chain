import type { AppProps } from "next/app"
import { Box } from "@chakra-ui/react"
import Head from 'next/head'
import Provider from "@components/shared/Provider"

function MyApp({ Component, pageProps }: AppProps) {
	return (
		<>
			<Head>
				<title>Chain Store</title>
			</Head>
			<Provider>
				<Component {...pageProps} />
			</Provider>
		</>
	)
}
export default MyApp
