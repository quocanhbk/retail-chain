import type { AppProps } from "next/app"
import { Box } from "@chakra-ui/react"
import Head from "next/head"
import Provider from "@components/shared/Provider"
import { NextPage } from "next"
import { ReactElement, ReactNode } from "react"
import "../styles.css"

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
				<title>BKRM Retail System</title>
			</Head>
			<Box h="100vh" overflow="hidden" bg="gray.50">
				{getLayout(<Component {...pageProps} />)}
			</Box>
		</Provider>
	)
}
export default MyApp
