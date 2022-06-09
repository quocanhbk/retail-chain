import { Grid, Heading } from "@chakra-ui/react"
import { EmployeeLayout } from "@components/module"
import { ReactElement } from "react"

const CartPage = () => {
	return (
		<Grid placeItems={"center"} h="full" pb={24}>
			<Heading color={"text.secondary"}>This page is under development</Heading>
		</Grid>
	)
}

CartPage.getLayout = function getLayout(page: ReactElement) {
	return <EmployeeLayout>{page}</EmployeeLayout>
}

export default CartPage
