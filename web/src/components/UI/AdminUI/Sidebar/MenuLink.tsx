import { Box } from "@chakra-ui/layout"
import Link from "next/link"

interface MenuLinkProps {
	href: string
	text: string
}

const MenuLink = ({ href, text }: MenuLinkProps) => {
	return (
		<Box cursor="pointer" color="gray.500">
			<Link href={href}>{text}</Link>
		</Box>
	)
}

export default MenuLink
