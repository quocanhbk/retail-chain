import { Flex, Grid, Heading, chakra } from "@chakra-ui/react"
import { useStoreState } from "@store"

const HomeUI = () => {
	const employeeInfo = useStoreState(s => s.employeeInfo)

	return (
		<Grid w="full" h="full" placeItems="center">
			<Flex align="center">
				<Heading>
					Hello <chakra.span color="blue.500">{employeeInfo?.name}</chakra.span>
				</Heading>
			</Flex>
		</Grid>
	)
}

export default HomeUI
