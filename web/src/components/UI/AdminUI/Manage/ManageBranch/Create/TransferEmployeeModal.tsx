import { CreateEmployeeInput, Employee, getBranches, getEmployees } from "@api"
import { Box, Button, Checkbox, HStack, Stack, Text, VStack } from "@chakra-ui/react"
import { ChakraModal, SearchInput } from "@components/shared"
import { employeeRoles } from "@constants"
import { useEffect, useState } from "react"
import { useQuery } from "react-query"

interface TransferEmployeeModalProps {
	isOpen: boolean
	onClose: () => void
	onSubmit: (data: { id: number; roles: string[] }[]) => void
	selectedEmployeeIds: number[]
}

const TransferEmployeeModal = ({ isOpen, onClose, onSubmit, selectedEmployeeIds }: TransferEmployeeModalProps) => {
	const [employeesData, setEmployeesData] = useState<
		(Pick<CreateEmployeeInput, "email" | "branch_id" | "name" | "phone" | "roles"> & { selected: boolean; id: number })[]
	>([])

	const initEmployeesData = (data: Employee[]) => {
		setEmployeesData([
			...data
				.filter(e => !selectedEmployeeIds.includes(e.id))
				.map(e => ({
					...e,
					id: e.id,
					roles: e.employment.roles.map(r => r.role),
					branch_id: e.employment.branch_id,
					selected: false
				}))
		])
	}

	const { data: employees, isLoading: isLoadingEmployees } = useQuery("employees", getEmployees, {
		initialData: [],
		onSuccess: initEmployeesData
	})

	const { data: branches, isLoading: isLoadingBranches } = useQuery("branches", () => getBranches(), { initialData: [] })

	const isLoading = isLoadingEmployees || isLoadingBranches

	const [searchText, setSearchText] = useState("")

	const handleSelect = (employeeId: number) => {
		const employee = employeesData.find(e => e.id === employeeId)
		if (employee) {
			employee.selected = !employee.selected
			setEmployeesData(employeesData.map(e => (e.id === employeeId ? employee : e)))
		}
	}

	const handleSelectRole = (employeeId: number, role: string) => {
		const employee = employeesData.find(e => e.id === employeeId)
		if (employee) {
			employee.roles = employee.roles.includes(role) ? employee.roles.filter(r => r !== role) : [...employee.roles, role]
			setEmployeesData(employeesData.map(e => (e.id === employeeId ? employee : e)))
		}
	}

	const handleSubmit = () => {
		const employees = employeesData.filter(e => e.selected).map(e => ({ id: e.id, roles: e.roles }))
		onSubmit(employees)
		onClose()
	}

	const render = () => {
		if (isLoading) {
			return <div>Loading...</div>
		}
		if (!employees || !branches) {
			return <div>Error</div>
		}
		return (
			<Box>
				<SearchInput value={searchText} onChange={e => setSearchText(e.target.value)} mb={2} />
				{branches
					.filter(branch => employeesData.some(employee => employee.branch_id === branch.id))
					.map(branch => (
						<Box key={branch.id}>
							<Text mb={2} fontWeight={"semibold"}>
								{branch.name}
							</Text>
							<VStack align="stretch">
								{employeesData
									.filter(employee => employee.branch_id === branch.id)
									.map(employee => (
										<Stack spacing={2} key={employee.id} direction="row">
											<Checkbox isChecked={employee.selected} onChange={() => handleSelect(employee.id)}>
												<Text w="10rem" isTruncated>
													{employee.name}
												</Text>
											</Checkbox>
											<Text color={"text.secondary"} w="12rem" flexShrink={0} isTruncated>
												{employee.email}
											</Text>
											<Text color={"text.secondary"} w="8rem" flexShrink={0} isTruncated>
												{employee.phone}
											</Text>
											<HStack flex={1} justify="flex-end">
												{employeeRoles.map(role => (
													<Checkbox
														key={role.id}
														isChecked={employee.roles.includes(role.id)}
														onChange={() => handleSelectRole(employee.id, role.id)}
													>
														{role.value}
													</Checkbox>
												))}
											</HStack>
										</Stack>
									))}
							</VStack>
						</Box>
					))}
				<HStack justify="flex-end" mt={8}>
					<Button onClick={handleSubmit}>{"Xác nhận"}</Button>
					<Button variant="ghost" onClick={onClose}>
						{"Hủy"}
					</Button>
				</HStack>
			</Box>
		)
	}

	useEffect(() => {
		if (isOpen) {
			employees && initEmployeesData(employees)
		}
	}, [isOpen])

	return (
		<ChakraModal isOpen={isOpen} onClose={onClose} title="Chuyển nhân viên" size="4xl">
			{render()}
		</ChakraModal>
	)
}

export default TransferEmployeeModal
