import { Flex } from "@chakra-ui/react"
import { useState } from "react"
import { useRouter } from "next/router"
import { useQuery } from "react-query"
import Header from "./Header"
import { useStoreActions } from "@store"
import { getStoreInfo } from "@api"
import { AnimatePresence } from "framer-motion"
import { LoadingScreen, Motion } from "@components/shared"
import { useTheme } from "@hooks"
interface AdminLayoutProps {
	children: React.ReactNode
}

const variants = {
	initial: {
		opacity: 0,
		x: "-100%",
		y: 0
	},
	animate: {
		opacity: 1,
		x: 0,
		y: 0
	},
	exit: {
		opacity: 0,
		x: 0,
		y: "100%"
	}
}

export const AdminLayout = ({ children }: AdminLayoutProps) => {
	const router = useRouter()
	const [loading, setLoading] = useState(true)
	const { backgroundPrimary } = useTheme()
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
		retry: false
	})

	return (
		<Flex direction="column" h="100vh">
			<LoadingScreen isLoading={loading} />
			<Flex direction="column" h="100vh">
				<Header />
				<AnimatePresence exitBeforeEnter initial={false}>
					<Motion.Flex
						flex={1}
						w="full"
						justify={"center"}
						overflow={"auto"}
						backgroundColor={backgroundPrimary}
						key={router.pathname}
						variants={variants}
						initial="initial"
						animate="animate"
						exit="exit"
						transition={{ type: "tween", duration: 0.25 }}
					>
						<Motion.Box w="full" maxW="64rem">
							{children}
						</Motion.Box>
					</Motion.Flex>
				</AnimatePresence>
			</Flex>
		</Flex>
	)
}

export default AdminLayout
