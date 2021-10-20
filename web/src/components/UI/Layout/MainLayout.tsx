import { Flex, Grid, Avatar, Text, Box, VStack, Heading } from "@chakra-ui/react"
import useStore from "@store"
import { MdPointOfSale, MdPeopleAlt, MdAnalytics, MdSettings } from "react-icons/md"
import { FaWarehouse } from "react-icons/fa"
import { useEffect, useState } from "react"
import { useRouter } from "next/router"
import { id } from "date-fns/locale"
interface indexProps {}

const menus = [
	{ id: "sale", text: "Bán hàng", icon: MdPointOfSale },
	{ id: "inventory", text: "Kho hàng", icon: FaWarehouse },
	{ id: "human-resource", text: "Nhân sự", icon: MdPeopleAlt },
	{ id: "management", text: "Quản lý", icon: MdAnalytics },
	{ id: "setting", text: "Thiết lập", icon: MdSettings },
]

interface MainLayoutProps {
	children: React.ReactNode
}

export const MainLayout = ({ children }: MainLayoutProps) => {
	const info = useStore(s => s.info)
	const router = useRouter()
	const currentPage = router.pathname.split("/")[1]
	const [loading, setLoading] = useState(true)
	useEffect(() => {
		if (!info?.token) router.push("/login")
		setLoading(false)
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
			<Flex direction="column" h="full" justify="space-between" px={2} py={4} bg="gray.800" align="center">
				<VStack spacing={8}>
					{menus.map(item => (
						<Flex
							key={item.id}
							cursor="pointer"
							align="center"
							color={item.id === currentPage ? "whiteAlpha.900" : "whiteAlpha.700"}
							_hover={{ color: "whiteAlpha.900" }}
							direction="column"
							onClick={() => router.push(item.id)}
						>
							<Box px={4} pb={2}>
								<item.icon size="2rem" />
							</Box>
							<Text fontSize="xs" fontWeight={item.id === currentPage ? "semibold" : "normal"}>
								{item.text}
							</Text>
						</Flex>
					))}
				</VStack>
				<Avatar name={info?.user_info.name} cursor="pointer" onClick={() => router.push("/setting")} />
			</Flex>
			<Box flex={1}>{children}</Box>
		</Flex>
	)
}

export default MainLayout
