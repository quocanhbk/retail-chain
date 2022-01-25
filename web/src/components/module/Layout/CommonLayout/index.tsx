import { BoxProps, Flex } from "@chakra-ui/react"
import { useRouter } from "next/router"
import Header, { HeaderProps } from "./Header"
import { AnimatePresence } from "framer-motion"
import { LoadingScreen, Motion } from "@components/shared"
import { useTheme } from "@hooks"

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

interface CommonLayoutProps extends HeaderProps {
	children: React.ReactNode
	isLoading?: boolean
	maxW?: BoxProps["maxW"]
}

export const CommonLayout = ({ children, isLoading, maxW = "64rem", ...headerProps }: CommonLayoutProps) => {
	const router = useRouter()
	const { backgroundPrimary } = useTheme()
	return (
		<Flex direction="column" h="100vh">
			<LoadingScreen isLoading={isLoading} />
			<Flex direction="column" h="100vh" backgroundColor={backgroundPrimary}>
				<Header {...headerProps} />
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
						<Motion.Box w="full" maxW={maxW}>
							{children}
						</Motion.Box>
					</Motion.Flex>
				</AnimatePresence>
			</Flex>
		</Flex>
	)
}

export default CommonLayout
