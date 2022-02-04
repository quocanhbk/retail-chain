import { AddingEmployee } from "@api"
import { Box, VStack, HStack, Text, IconButton, Stack } from "@chakra-ui/react"
import { RoleTag } from "@components/shared"
import { employeeRoles } from "@constants"
import { BsX } from "react-icons/bs"

interface EmployeesTableProps {
	employees: AddingEmployee[]
	onRemove: (email: string) => void
}

const EmployeesTable = ({ employees, onRemove }: EmployeesTableProps) => {
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
						<Text
							isTruncated
							color={e.type === "create" ? "fill.success" : "fill.warning"}
							textTransform={"uppercase"}
							w="5rem"
							flexShrink={0}
						>
							{e.type === "create" ? "Tạo mới" : "Chuyển"}
						</Text>
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
							{e.roles.map(role => (
								<RoleTag key={role} role={employeeRoles.find(r => r.id === role)!} />
							))}
						</HStack>
						<IconButton
							icon={<BsX size="1.25rem" />}
							aria-label="remove-employee"
							onClick={() => onRemove(e.email)}
							size="xs"
							rounded="full"
							colorScheme={"red"}
							variant="ghost"
						/>
					</Stack>
				))}
			</VStack>
		</Box>
	)
}

export default EmployeesTable
