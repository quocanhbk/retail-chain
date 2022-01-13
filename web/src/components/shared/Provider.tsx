import { ChakraProvider } from "@chakra-ui/react"
import store from "@store"
import { StoreProvider } from "easy-peasy"
import { QueryClient, QueryClientProvider } from "react-query"
import theme from "src/theme"

interface ProviderProps {
	children: React.ReactNode
}

const queryClient = new QueryClient({
	defaultOptions: {
		queries: {
			keepPreviousData: true,
		},
	},
})

export const Provider = ({ children }: ProviderProps) => {
	return (
		<StoreProvider store={store}>
			<QueryClientProvider client={queryClient}>
				<ChakraProvider theme={theme}>{children}</ChakraProvider>
			</QueryClientProvider>
		</StoreProvider>
	)
}

export default Provider
