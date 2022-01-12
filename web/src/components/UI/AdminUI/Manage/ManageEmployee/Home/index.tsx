import { Employee, getBranches, getEmployees } from "@api"
import { Box, Button, Flex, Heading, VStack } from "@chakra-ui/react"
import { useQuery } from "react-query"
import BranchAccordion from "./BranchAccordion"
import BranchAccordionSkeleton from "./BranchAccordionSkeleton"
import Link from "next/link"
import { SearchInput } from "@components/shared"
import { useState } from "react"
import { useThrottle } from "@hooks"

const HomeEmployeeUI = () => {
	const { data: branches, isLoading: isLoadingBranches } = useQuery("branches", () => getBranches())
	const { data: employees, isLoading: isLoadingEmployees } = useQuery("employees", () => getEmployees())
	const isLoading = isLoadingBranches || isLoadingEmployees
	const isError = !branches || !employees

	const [searchText, setSearchText] = useState("")

	const getBranchName = branchId => (branches ? branches.find(branch => branch.id === branchId)?.name : "")
	const getEmployeeSeachValue = (employee: Employee) =>
		`${employee.name} ${employee.phone} ${employee.email} ${employee.employment.roles
			.map(r => r.role)
			.join(" ")} ${getBranchName(employee.employment.branch_id)}`.toLowerCase()

	const render = () => {
		if (isLoading)
			return (
				<VStack align="stretch">
					{Array(5)
						.fill(null)
						.map((_, index) => (
							<BranchAccordionSkeleton key={index} />
						))}
				</VStack>
			)
		if (isError) return <Box>Error</Box>
		return (
			<VStack align="stretch">
				{branches.map(branch => (
					<BranchAccordion
						key={branch.id}
						data={branch}
						employees={employees.filter(
							employee =>
								employee.employment.branch_id === branch.id &&
								getEmployeeSeachValue(employee).includes(searchText.toLowerCase())
						)}
					/>
				))}
			</VStack>
		)
	}

	return (
		<Box p={4}>
			<Flex w="full" align="center" justify="space-between">
				<Heading mb={4} fontSize={"2xl"}>
					{"Quản lý nhân viên"}
				</Heading>

				<Link href="/admin/manage/employee/create">
					<Button size="sm" variant="ghost">
						{"Tạo nhân viên"}
					</Button>
				</Link>
			</Flex>
			<SearchInput
				value={searchText}
				onChange={e => setSearchText(e.target.value)}
				placeholder="Tìm kiếm nhân viên"
				mb={2}
				onClear={() => setSearchText("")}
			/>
			{render()}
		</Box>
	)
}

export default HomeEmployeeUI
