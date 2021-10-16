import { ChakraProvider } from "@chakra-ui/react"
import { QueryClient, QueryClientProvider } from "react-query"
import theme from "src/theme"

interface ProviderProps {
	children: React.ReactNode
}

const queryClient = new QueryClient()

export const Provider = ({ children }: ProviderProps) => {
	return (
		<QueryClientProvider client={queryClient}>
			<ChakraProvider theme={theme}>{children}</ChakraProvider>
		</QueryClientProvider>
	)
}

export default Provider
