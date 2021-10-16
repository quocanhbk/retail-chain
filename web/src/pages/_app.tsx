import type { AppProps } from "next/app"
import { Box } from "@chakra-ui/react"
import Provider from "@components/shared/Provider"

function MyApp({ Component, pageProps }: AppProps) {
	return (
		<Provider>
			<Box h="100vh" overflow="hidden">
				<Component {...pageProps} />
			</Box>
		</Provider>
	)
}
export default MyApp
