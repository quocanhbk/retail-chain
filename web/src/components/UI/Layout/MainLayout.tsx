import { Flex, Grid, Avatar, Text, Box, VStack, Heading } from "@chakra-ui/react"
import { MdPointOfSale, MdPeopleAlt, MdAnalytics, MdSettings } from "react-icons/md"
import { FaWarehouse } from "react-icons/fa"
import { useState } from "react"
import { useRouter } from "next/router"
import { useQuery } from "react-query"
import { me } from "@api"
interface indexProps {}

// const menus = [
// 	{ id: "sale", text: "Bán hàng", icon: MdPointOfSale },
// 	{ id: "inventory", text: "Kho hàng", icon: FaWarehouse },
// 	{ id: "human-resource", text: "Nhân sự", icon: MdPeopleAlt },
// 	{ id: "management", text: "Quản lý", icon: MdAnalytics },
// 	{ id: "setting", text: "Thiết lập", icon: MdSettings },
// ]

interface MainLayoutProps {
	children: React.ReactNode
}

export const MainLayout = ({ children }: MainLayoutProps) => {
	const router = useRouter()
	// const currentPage = router.pathname.split("/")[1]
	const [loading, setLoading] = useState(true)

	useQuery("me", () => me(), {
		enabled: loading,
		onSuccess: () => {
			setLoading(false)
		},
		onError: () => {
			router.push("/")
			setLoading(false)
		},
		retry: false,
	})

	if (loading) {
		return (
			<Grid w="full" h="full" placeItems="center">
				<Heading>Loading</Heading>
			</Grid>
		)
	}

	return (
		<Flex w="full" h="full">
			{children}
		</Flex>
	)
}

export default MainLayout
