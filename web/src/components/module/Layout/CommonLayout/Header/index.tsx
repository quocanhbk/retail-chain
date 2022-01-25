import { Flex, Heading, HStack } from "@chakra-ui/react"
import { Motion } from "@components/shared"
import { useTheme } from "@hooks"
import NavMenus from "./NavMenus"
import StoreInfo from "./StoreInfo"
import SubNavMenus from "./SubNavMenus"

export interface HeaderProps {
	title: string
	menus: { id: string; text: string; path: string }[]
	subNavmenus: { id: string; text: string; path: string }[]
	name: string
	onLogout: () => void
}

export const Header = ({ title, menus, subNavmenus, name, onLogout }: HeaderProps) => {
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
					{title}
				</Heading>
				<HStack align="center" spacing={8}>
					<NavMenus menus={menus} />
					<StoreInfo name={name} onLogout={onLogout} />
				</HStack>
			</Flex>
			<Motion.Box key={subNavmenus.length}>
				<SubNavMenus menu={subNavmenus ?? []} />
			</Motion.Box>
		</Flex>
	)
}

export default Header
