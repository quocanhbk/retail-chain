import { Grid, Flex, Heading, Box } from "@chakra-ui/react"
import { useState } from "react"
import { useRouter } from "next/router"
import { useQuery } from "react-query"
import Header from "./Header"
import { useStoreActions } from "@store"
import { getStoreInfo } from "@api"
import { AnimatePresence } from "framer-motion"
import { LoadingScreen, Motion } from "@components/shared"
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

	return (
		<Flex direction="column" h="100vh">
			<AnimatePresence>
				{loading ? (
					<Motion.Box initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} h="full">
						<LoadingScreen />
					</Motion.Box>
				) : (
					<>
						<Header />
						<Flex flex={1} w="full" justify={"center"} overflow={"auto"}>
							<Box w="full" maxW="64rem">
								{children}
							</Box>
						</Flex>
					</>
				)}
			</AnimatePresence>
		</Flex>
	)
}

export default AdminLayout
