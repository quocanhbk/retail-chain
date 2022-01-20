import { Employee, getEmployeeAvatar } from "@api"
import { Avatar, Flex, HStack, Text } from "@chakra-ui/react"
import { RoleTag } from "@components/shared"
import { employeeRoles } from "@constants"
import { useTheme } from "@hooks"
import Link from "next/link"

interface EmployeeCardProps {
	data: Employee
}

const EmployeeCard = ({ data }: EmployeeCardProps) => {
	const { textSecondary, backgroundSecondary } = useTheme()

	return (
		<Link href={`/admin/manage/employee/${data.id}`}>
			<Flex
				key={data.id}
				rounded="md"
				align="center"
				w="full"
				cursor="pointer"
				_hover={{ bg: backgroundSecondary }}
				px={2}
				py={2}
			>
				<Avatar size="xs" src={getEmployeeAvatar(data.avatar_key)} alt={data.name} mr={2} />
				<Text w="15rem" isTruncated mr={2} flexShrink={0}>
					{data.name}
				</Text>
				<Text fontSize={"sm"} color={textSecondary} w="15rem" isTruncated flexShrink={0} mr={2}>
					{data.email}
				</Text>
				<Text fontSize={"sm"} color={textSecondary} w="8rem" isTruncated flexShrink={0}>
					{data.phone}
				</Text>
				<HStack justify="flex-end" flex={1}>
					{data.employment.roles.map(role => (
						<RoleTag key={role.id} role={employeeRoles.find(r => r.id === role.role)!} />
					))}
				</HStack>
			</Flex>
		</Link>
	)
}

export default EmployeeCard
