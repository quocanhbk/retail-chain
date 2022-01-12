import { Employee } from "@api"
import { Avatar, Box, Flex, HStack, Text } from "@chakra-ui/react"
import { employeeRoles } from "@constants"
import { baseURL } from "src/api/fetcher"

interface EmployeeCardProps {
	data: Employee
}

const EmployeeCard = ({ data }: EmployeeCardProps) => {
	return (
		<Flex key={data.id} bg="white" rounded="md" shadow="base" p={2} align="center" w="full">
			<Avatar size="xs" src={`${baseURL}/employee/avatar/${data.id}`} alt={data.name} mr={2} />
			<Text fontWeight={500} w="15rem" isTruncated mr={2} flexShrink={0}>
				{data.name}
			</Text>
			<Text fontSize={"sm"} color="blackAlpha.600" w="15rem" isTruncated flexShrink={0} mr={2}>
				{data.email}
			</Text>
			<Text fontSize={"sm"} color="blackAlpha.600" w="8rem" isTruncated flexShrink={0}>
				{data.phone}
			</Text>
			<HStack justify="flex-end" flex={1}>
				{data.employment.roles.map(role => (
					<Box key={role.id} bg="telegram.100" px={2} py={0.5} rounded="sm">
						<Text fontSize={"sm"}>{employeeRoles.find(r => r.id === role.role)!.value}</Text>
					</Box>
				))}
			</HStack>
		</Flex>
	)
}

export default EmployeeCard
