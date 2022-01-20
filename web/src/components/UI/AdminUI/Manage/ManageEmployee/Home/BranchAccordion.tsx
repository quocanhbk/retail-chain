import { Branch, Employee } from "@api"
import { Box, Flex, Text, VStack } from "@chakra-ui/react"
import { useTheme } from "@hooks"
import EmployeeCard from "./EmployeeCard"

interface BranchAccordionProps {
	data: Branch
	employees: Employee[]
}

const BranchAccordion = ({ data: branch, employees }: BranchAccordionProps) => {
	const { textSecondary } = useTheme()
	const renderEmployees = () => {
		if (employees.length === 0)
			return (
				<Text color={textSecondary} fontSize={"sm"}>
					{"Không có nhân viên tại chi nhánh này"}
				</Text>
			)
		return employees.map(employee => <EmployeeCard key={employee.id} data={employee} />)
	}

	return (
		<Box key={branch.id} overflow={"hidden"}>
			<Flex
				align="center"
				justify="space-between"
				py={1}
				transition={"color 0.25s ease-in-out"}
				cursor={"pointer"}
			>
				<Text>{branch.name}</Text>
			</Flex>
			<VStack align="stretch" py={2} borderTop={"1px"} borderColor={"blackAlpha.200"} spacing={0}>
				{renderEmployees()}
			</VStack>
		</Box>
	)
}

export default BranchAccordion
