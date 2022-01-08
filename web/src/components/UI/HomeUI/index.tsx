import { Flex, Grid, Heading, chakra } from "@chakra-ui/react"
import { MdPointOfSale, MdPeopleAlt, MdAnalytics } from "react-icons/md"
import { FaWarehouse } from "react-icons/fa"
import { useStoreState } from "@store"

// const menus = [
// 	{ id: "sale", text: "Bán hàng", icon: MdPointOfSale },
// 	{ id: "warehouse", text: "Kho hàng", icon: FaWarehouse },
// 	{ id: "human-resource", text: "Nhân sự", icon: MdPeopleAlt },
// 	{ id: "managing", text: "Quản lý", icon: MdAnalytics },
// ]

const HomeUI = () => {
	const info = useStoreState(s => s.info)

	return (
		<Grid w="full" h="full" placeItems="center">
			<Flex align="center">
				<Heading>
					Hello <chakra.span color="blue.500">{info?.name}</chakra.span>
				</Heading>
			</Flex>
		</Grid>
	)
}

export default HomeUI
