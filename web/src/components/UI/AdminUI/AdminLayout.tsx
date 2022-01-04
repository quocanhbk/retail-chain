import { Grid, Flex, Heading, Box } from "@chakra-ui/react"
import { useState } from "react"
import { useRouter } from "next/router"
import { useQuery } from "react-query"
import Header from "./Header"
import { useStoreActions } from "@store"
import { getStoreInfo } from "@api"
interface AdminLayoutProps {
	children: React.ReactNode
}

export const AdminLayout = ({ children }: AdminLayoutProps) => {
	const router = useRouter()
	const [loading, setLoading] = useState(true)

	const setInfo = useStoreActions(action => action.setInfo)

	useQuery("store-info", () => getStoreInfo(), {
		enabled: loading,
		onSuccess: data => {
			setInfo(data)
			setLoading(false)
		},
		onError: () => {
			router.push("/login")
			setLoading(false)
		},
		retry: false,
	})

	if (loading) {
		return (
			<Grid w="full" h="full" placeItems="center">
				<Heading>Loading</Heading>
			</Grid>
		)
	}

	return (
		<Flex direction="column" h="100vh">
			<Header />
			<Flex flex={1} w="full" justify={"center"}>
				<Box w="full" maxW="64rem">
					{children}
				</Box>
			</Flex>
		</Flex>
	)
}

export default AdminLayout
