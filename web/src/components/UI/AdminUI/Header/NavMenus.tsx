import { Stack, Text } from "@chakra-ui/react"
import Link from "next/link"
import { useRouter } from "next/router"

interface NavMenusProps {
	menus: { id: string; text: string; path: string }[]
}

const NavMenus = ({ menus }: NavMenusProps) => {
	const router = useRouter()
	const currentPath = router.pathname.split("/")[2] || ""
	return (
		<Stack direction="row" spacing={8}>
			{menus.map(menu => (
				<Link href={menu.path} key={menu.id}>
					<Text
						color={menu.id === currentPath ? "telegram.600" : "blackAlpha.700"}
						fontWeight={500}
						cursor={"pointer"}
					>
						{menu.text}
					</Text>
				</Link>
			))}
		</Stack>
	)
}

export default NavMenus
