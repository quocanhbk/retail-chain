import { Employee } from "@api"
import { Box, VStack, HStack, Text, IconButton, Stack } from "@chakra-ui/react"
import { RoleTag } from "@components/shared"
import { employeeRoles } from "@constants"
import { BsChevronRight } from "react-icons/bs"
import Link from "next/link"

interface EmployeesTableProps {
	employees: Employee[]
}

const EmployeesTable = ({ employees }: EmployeesTableProps) => {
	return (
		<Box border="1px" borderColor={"border.primary"} h="18rem" rounded="md">
			<VStack p={2} spacing={0}>
				{employees.map(e => (
					<Stack
						direction="row"
						key={e.email}
						rounded="md"
						align="center"
						w="full"
						cursor="pointer"
						_hover={{ bg: "background.secondary" }}
						p={2}
					>
						<Text isTruncated w="12rem" flexShrink={0}>
							{e.name}
						</Text>
						<Text fontSize={"sm"} color={"text.secondary"} isTruncated w="12rem" flexShrink={0}>
							{e.email}
						</Text>
						<Text fontSize={"sm"} color={"text.secondary"} isTruncated w="8rem" flexShrink={0}>
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
