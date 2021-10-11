import type { AppProps } from "next/app"
import { Box } from "@chakra-ui/react"
import Provider from "@components/shared/Provider"

function MyApp({ Component, pageProps }: AppProps) {
	return (
		<Provider>
			<Component {...pageProps} />
		</Provider>
	)
}
export default MyApp
