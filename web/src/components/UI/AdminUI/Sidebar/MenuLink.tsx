import { Box } from "@chakra-ui/layout"
import {Icon, Link } from "@chakra-ui/react"
import NextLink from "next/link"
import { IconType } from "react-icons"

interface MenuLinkProps {
	href: string
	text: string
	icon: IconType
	active: boolean
}

const MenuLink = ({ href, text,icon,active }: MenuLinkProps) => {
	return (
		<Box cursor="pointer" color="gray.500" mb={4}>
			<NextLink href={href} passHref>
				<Link fontSize="18px" color={active ? "#109CF1" : "#334D6E"}>
					<Icon as={icon} h={6} w={6} mr={3} />
					{text}
				</Link>
			</NextLink>
		</Box>
	)
}

export default MenuLink
