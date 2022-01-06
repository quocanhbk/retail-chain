import { Flex, Grid, Heading, Spinner, Text } from "@chakra-ui/react"

export const LoadingScreen = () => {
	return (
		<Grid w="full" h="full" placeItems={"center"} pb={24}>
			<Flex direction="column" align="center">
				<Heading
					fontSize="4xl"
					backgroundColor="telegram.500"
					color="white"
					rounded="md"
					px={2}
					py={1}
					fontWeight={"900"}
					fontFamily={"Brandon"}
					mb={4}
				>
					BKRM ADMIN
				</Heading>
				<Flex align="center">
					<Spinner color="telegram.500" size="sm" thickness="3px" />
					<Text ml={2}>Loading</Text>
				</Flex>
			</Flex>
		</Grid>
	)
}

export default LoadingScreen
