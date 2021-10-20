import { logout } from "@api"
import { Flex, Grid, Heading, Button, chakra, Avatar, Text, Box, VStack } from "@chakra-ui/react"
import useStore from "@store"
import { MdPointOfSale, MdPeopleAlt, MdAnalytics } from "react-icons/md"
import { FaWarehouse } from "react-icons/fa"
import { useMutation } from "react-query"
import { motion } from "framer-motion"
interface indexProps {}

const menus = [
	{ id: "sale", text: "Bán hàng", icon: MdPointOfSale },
	{ id: "warehouse", text: "Kho hàng", icon: FaWarehouse },
	{ id: "human-resource", text: "Nhân sự", icon: MdPeopleAlt },
	{ id: "managing", text: "Quản lý", icon: MdAnalytics },
]

const HomeUI = ({}: indexProps) => {
	const { initInfo, info } = useStore(s => ({ initInfo: s.initInfo, info: s.info }))
	const { mutate } = useMutation(logout, {
		onSuccess: () => {
			initInfo()
		},
	})

	return (
		<Grid w="full" h="full" placeItems="center">
			<Flex align="center">
				<Heading>
					Hello <chakra.span color="blue.500">{info?.user_info.username}</chakra.span>
				</Heading>
				<Button variant="outline" ml={4} size="sm" onClick={() => mutate()}>
					Log out
				</Button>
			</Flex>
		</Grid>
	)
}

export default HomeUI
