import { Flex, Heading, HStack } from "@chakra-ui/react"
import { Motion } from "@components/shared"
import { adminNavMenus, employeeRoles } from "@constants"
import { useTheme } from "@hooks"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import NavMenus from "./NavMenus"
import EmployeeInfo from "./EmployeeInfo"
import SubNavMenus from "./SubNavMenus"

export const Header = () => {
	const [selectedMenu, setSelectedMenu] = useState("")
	const router = useRouter()
	useEffect(() => {
		if (router.isReady) setSelectedMenu(router.pathname.split("/")[2] || "")
	}, [router.isReady, router.pathname])

	const { backgroundSecondary } = useTheme()

	return (
		<Flex direction="column" shadow="xs" bg={backgroundSecondary}>
			<Flex align="center" w="full" justify="space-between" px={4} py={2} shadow="xs">
				<Heading
					fontSize="2xl"
					backgroundColor="telegram.500"
					color="white"
					rounded="md"
					px={2}
					py={1}
					fontWeight={"900"}
					fontFamily={"Brandon"}
				>
					BKRM
				</Heading>
				<HStack align="center" spacing={8}>
					{/* <NavMenus menus={adminNavMenus} /> */}
					<EmployeeInfo />
				</HStack>
			</Flex>
			<SubNavMenus />
			{/* <Motion.Box key={adminNavMenus.find(m => m.id === selectedMenu)?.subMenus.length}>
				<SubNavMenus menu={adminNavMenus.find(m => m.id === selectedMenu)?.subMenus ?? []} />
			</Motion.Box> */}
		</Flex>
	)
}

export default Header
