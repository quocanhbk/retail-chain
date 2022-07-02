import type { AppProps } from "next/app"
import { Box } from "@chakra-ui/react"
import Head from "next/head"
import Provider, { getServerSideProps } from "@components/shared/Provider"
import { NextPage } from "next"
import { ReactElement, ReactNode } from "react"
import "../styles.css"
import { LoadableContainer, LoadingScreen } from "@components/shared"
import { useQuery } from "react-query"
import { client } from "@api"
import { useRouter } from "next/router"

export type NextPageWithLayout = NextPage & {
  getLayout?: (page: ReactElement) => ReactNode
}

type AppPropsWithLayout = AppProps & {
  Component: NextPageWithLayout
}

const MyApp = ({ Component, pageProps }: AppPropsWithLayout) => {
  const getLayout = Component.getLayout || (page => page)
  return (
    <Provider cookies={pageProps.cookies}>
      <Head>
        <title>BKRM Retail System</title>
      </Head>
      <LoadingScreen>
        <Box h="100vh" overflow="hidden">
          {getLayout(<Component {...pageProps} />)}
        </Box>
      </LoadingScreen>
    </Provider>
  )
}

export { getServerSideProps }

export default MyApp
