import { Branch, Employee } from "@api"
import { Box, Button, Flex, HStack, Text, VStack } from "@chakra-ui/react"
import { useState } from "react"
import { BsPerson } from "react-icons/bs"
import Link from "next/link"
import EmployeeCard from "./EmployeeCard"

interface BranchAccordionProps {
	data: Branch
	employees: Employee[]
}

const BranchAccordion = ({ data: branch, employees }: BranchAccordionProps) => {
	const [isOpen, setIsOpen] = useState(false)

	const renderEmployees = () => {
		if (employees.length === 0)
			return (
				<Text color="blackAlpha.600" fontSize={"sm"}>
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
				color={isOpen ? "telegram.600" : "black"}
				transition={"color 0.25s ease-in-out"}
				cursor={"pointer"}
				onClick={() => setIsOpen(!isOpen)}
			>
				<Text>{branch.name}</Text>
			</Flex>
			<VStack align="stretch" py={2} borderTop={"1px"} borderColor={"blackAlpha.200"}>
				{renderEmployees()}
			</VStack>
		</Box>
	)
}

export default BranchAccordion
