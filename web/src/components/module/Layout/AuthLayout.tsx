import { Box, Flex, Grid, Text } from "@chakra-ui/react"
import Image from "next/image"
import { ReactNode } from "react"
import retailImage from "./retail.jpg"

interface AuthLayoutProps {
	children: ReactNode
}

export const AuthLayout = ({ children }: AuthLayoutProps) => {
	return (
		<Box h="100vh" pos="relative">
			<Image src={retailImage} layout="fill" />
			<Flex
				h="full"
				direction={["column", "column", "row"]}
				overflow="auto"
				align={["center", "center", "stretch"]}
				pos="relative"
				zIndex={2}
			>
				<Grid placeItems="center" flex={[1, 2]} p={8} display={["none", "none", "flex"]} bg="blackAlpha.900">
					<Text fontSize={["4rem", "5rem", "6rem", "7rem"]} fontWeight="black" color="white" fontFamily="Brandon">
						BKRM RETAIL MANAGEMENT SYSTEM
					</Text>
				</Grid>
				<Flex direction="column" justify="center" w="24rem" p={8} h="full" bg="whiteAlpha.900">
					{children}
				</Flex>
			</Flex>
		</Box>
	)
}

export default AuthLayout
