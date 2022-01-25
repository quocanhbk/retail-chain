import { Flex, Grid, Heading, Spinner, Text } from "@chakra-ui/react"
import { useTheme } from "@hooks"
import { AnimatePresence } from "framer-motion"
import { Motion } from "."

interface LoadingScreenProps {
	isLoading?: boolean
	isAdmin?: boolean
}

export const LoadingScreen = ({ isLoading, isAdmin }: LoadingScreenProps) => {
	const { backgroundSecondary } = useTheme()

	return (
		<AnimatePresence exitBeforeEnter initial={false}>
			{isLoading && (
				<Motion.Box
					initial={{ opacity: 0, y: "100%" }}
					animate={{ opacity: 1, y: "0%" }}
					exit={{ opacity: 0, y: "100%" }}
					transition={{ duration: 0.5 }}
					h="100vh"
					w="full"
					pos="fixed"
					zIndex={"overlay"}
					bg={backgroundSecondary}
				>
					<Grid w="full" h="full" placeItems={"center"} pb={24}>
						<Flex direction="column" align="center">
							<Heading
								fontSize="4xl"
								backgroundColor="telegram.600"
								color="white"
								rounded="md"
								px={2}
								py={1}
								fontWeight={"900"}
								fontFamily={"Brandon"}
								mb={4}
							>
								BKRM{isAdmin&& "ADMIN"} 
							</Heading>
							<Flex align="center">
								<Spinner color="telegram.500" size="sm" thickness="3px" />
								<Text ml={2}>Loading</Text>
							</Flex>
						</Flex>
					</Grid>
				</Motion.Box>
			)}
		</AnimatePresence>
	)
}

export default LoadingScreen
