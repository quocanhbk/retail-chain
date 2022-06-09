import { ChakraProvider, cookieStorageManager, localStorageManager } from "@chakra-ui/react"
import store from "@store"
import { StoreProvider } from "easy-peasy"
import { QueryClient, QueryClientProvider } from "react-query"
import theme from "src/theme"

interface ProviderProps {
	cookies?: string
	children: React.ReactNode
}

const queryClient = new QueryClient({
	defaultOptions: {
		queries: {
			keepPreviousData: true,
			retry: false
		}
	}
})

export const Provider = ({ cookies, children }: ProviderProps) => {
	const colorModeManager = typeof cookies === "string" ? cookieStorageManager(cookies) : localStorageManager

	return (
		<StoreProvider store={store}>
			<QueryClientProvider client={queryClient}>
				<ChakraProvider theme={theme} colorModeManager={colorModeManager}>
					{children}
				</ChakraProvider>
			</QueryClientProvider>
		</StoreProvider>
	)
}

export function getServerSideProps({ req }) {
	return {
		props: {
			cookies: req.headers.cookie ?? ""
		}
	}
}

export default Provider
