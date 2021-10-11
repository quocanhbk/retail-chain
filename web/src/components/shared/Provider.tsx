import { ChakraProvider } from "@chakra-ui/react"
import { QueryClient, QueryClientProvider } from "react-query"

interface ProviderProps {
    children: React.ReactNode
}

const queryClient = new QueryClient()

const Provider = ({ children }: ProviderProps) => {
    return (
        <QueryClientProvider client={queryClient}>
            <ChakraProvider>{children}</ChakraProvider>
        </QueryClientProvider>
    )
}

export default Provider
