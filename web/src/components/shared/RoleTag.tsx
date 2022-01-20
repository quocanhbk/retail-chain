import { Role } from "@@types"
import { Tag } from "@chakra-ui/react"

interface RoleTagProps {
	role: Role
	onClick?: () => void
}

export const RoleTag = ({ role, onClick }: RoleTagProps) => {
	return (
		<Tag
			colorScheme={role.id === "manage" ? "blue" : role.id === "sale" ? "green" : "pink"}
			w="5.5rem"
			justifyContent={"center"}
			cursor={"pointer"}
			onClick={onClick}
		>
			{role.value}
		</Tag>
	)
}

export default RoleTag
