import { Employee } from "@api"
import { Box, VStack, HStack, Text, IconButton, Stack } from "@chakra-ui/react"
import { RoleTag } from "@components/shared"
import { employeeRoles } from "@constants"
import { useTheme } from "@hooks"
import { BsChevronRight } from "react-icons/bs"
import Link from "next/link"

interface EmployeesTableProps {
	employees: Employee[]
}

const EmployeesTable = ({ employees }: EmployeesTableProps) => {
	const { backgroundSecondary, textSecondary, borderPrimary } = useTheme()

	return (
		<Box border="1px" borderColor={borderPrimary} h="18rem" rounded="md">
			<VStack p={2} spacing={0}>
				{employees.map(e => (
					<Stack
						direction="row"
						key={e.email}
						rounded="md"
						align="center"
						w="full"
						cursor="pointer"
						_hover={{ bg: backgroundSecondary }}
						p={2}
					>
						<Text isTruncated w="12rem" flexShrink={0}>
							{e.name}
						</Text>
						<Text fontSize={"sm"} color={textSecondary} isTruncated w="12rem" flexShrink={0}>
							{e.email}
						</Text>
						<Text fontSize={"sm"} color={textSecondary} isTruncated w="8rem" flexShrink={0}>
							{e.phone}
						</Text>
						<HStack justify="flex-end" flex={1}>
							{e.employment.roles.map(role => (
								<RoleTag key={role.id} role={employeeRoles.find(r => r.id === role.role)!} />
							))}
						</HStack>
						<Link href={`/admin/manage/employee/${e.id}`}>
							<IconButton
								icon={<BsChevronRight size="1rem" />}
								aria-label="remove-employee"
								size="xs"
								rounded="full"
								variant="ghost"
								colorScheme={"gray"}
							/>
						</Link>
					</Stack>
				))}
			</VStack>
		</Box>
	)
}

export default EmployeesTable
