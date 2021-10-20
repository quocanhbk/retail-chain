import type { AppProps } from "next/app"
import { Box } from "@chakra-ui/react"
import Head from "next/head"
import Provider from "@components/shared/Provider"
import { NextPage } from "next"
import { ReactElement, ReactNode } from "react"

export type NextPageWithLayout = NextPage & {
	getLayout?: (page: ReactElement) => ReactNode
}

type AppPropsWithLayout = AppProps & {
	Component: NextPageWithLayout
}

const MyApp = ({ Component, pageProps }: AppPropsWithLayout) => {
	const getLayout = Component.getLayout || (page => page)

	return (
		<Provider>
			<Head>
				<title>Chain Store</title>
			</Head>
			<Box h="100vh" overflow="hidden">
				{getLayout(<Component {...pageProps} />)}
			</Box>
		</Provider>
	)
}
export default MyApp
