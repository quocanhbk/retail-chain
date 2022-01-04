import { Stack, Text } from "@chakra-ui/react"
import Link from "next/link"
import { useRouter } from "next/router"

const menus = [
	{ id: "", text: "Trang chủ", path: "/admin" },
	{ id: "manage", text: "Quản lý", path: "/admin/manage" },
]

const NavMenus = () => {
	const router = useRouter()
	const currentPath = router.pathname.split("/")[2] || ""
	return (
		<Stack direction="row" spacing={8}>
			{menus.map(menu => (
				<Link href={menu.path} key={menu.id}>
					<Text
						color={menu.id === currentPath ? "black" : "gray.600"}
						fontWeight={"semibold"}
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
