import { Grid, Box, Heading } from "@chakra-ui/react"
import { useState } from "react"
import { useRouter } from "next/router"
import { useQuery } from "react-query"
import { me, meAdmin } from "@api"
import useStore from "@store"
interface indexProps {}

interface AdminLayoutProps {
	children: React.ReactNode
}

export const AdminLayout = ({ children }: AdminLayoutProps) => {
	const router = useRouter()
	const [loading, setLoading] = useState(true)
	const setInfo = useStore(s => s.setInfo)

	useQuery("meAdmin", () => meAdmin(), {
		enabled: loading,
		onSuccess: data => {
			setInfo(data.info)
			setLoading(false)
		},
		onError: () => {
			router.push("/admin/login")
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

	return <Box>{children}</Box>
}

export default AdminLayout
