import { Grid, Heading } from "@chakra-ui/react"
import { useTheme } from "@hooks"

const StoreDashboardUI = () => {
	const theme = useTheme()

	return (
		<Grid h="full" placeItems={"center"}>
			<Heading color={theme.textSecondary}>This page is under development</Heading>
		</Grid>
	)
}

export default StoreDashboardUI
