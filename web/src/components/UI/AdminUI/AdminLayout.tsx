import { Grid, Flex, Heading, Box } from "@chakra-ui/react"
import { useState } from "react"
import { useRouter } from "next/router"
import { useQuery } from "react-query"
import { meAsAdmin } from "@api"
import { useStoreActions } from "@store"
import Sidebar from "./Sidebar"
import Header from "./Header"

interface AdminLayoutProps {
	children: React.ReactNode
}

export const AdminLayout = ({ children }: AdminLayoutProps) => {
	const router = useRouter()
	const [loading, setLoading] = useState(true)

	const setInfo = useStoreActions(a => a.setInfo)

	useQuery("meAdmin", () => meAsAdmin(), {
		enabled: loading,
		onSuccess: data => {
			setInfo(data.info)
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
			<Flex flex={1}>
				<Sidebar />
				<Box flex={1}>{children}</Box>
			</Flex>
		</Flex>
	)
}

export default AdminLayout
