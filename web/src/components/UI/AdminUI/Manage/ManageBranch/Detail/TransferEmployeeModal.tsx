import { CreateEmployeeInput, Employee, getBranches, getEmployees, transferManyEmployees } from "@api"
import { Box, Button, Checkbox, HStack, Stack, Text, VStack } from "@chakra-ui/react"
import { ChakraModal, SearchInput } from "@components/shared"
import { employeeRoles } from "@constants"
import { useChakraToast } from "@hooks"
import { useEffect, useState } from "react"
import { useMutation, useQuery, useQueryClient } from "react-query"

interface TransferEmployeeModalProps {
	isOpen: boolean
	onClose: () => void
	branch_id: number
}

const TransferEmployeeModal = ({ isOpen, onClose, branch_id }: TransferEmployeeModalProps) => {
	const toast = useChakraToast()
	const qc = useQueryClient()

	const [employeesData, setEmployeesData] = useState<
		(Pick<CreateEmployeeInput, "email" | "branch_id" | "name" | "phone" | "roles"> & { selected: boolean; id: number })[]
	>([])

	const initEmployeesData = (data: Employee[]) => {
		setEmployeesData([
			...data
				.filter(e => e.employment.branch_id !== branch_id)
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

	const { mutate, isLoading: isTransferringEmployees } = useMutation(
		() =>
			transferManyEmployees({
				branch_id,
				employees: employeesData
					.filter(employee => employee.selected)
					.map(employee => ({
						id: employee.id,
						roles: employee.roles
					}))
			}),
		{
			onSuccess: () => {
				toast({
					title: "Chuyển nhân viên thành công",
					status: "success"
				})
				qc.invalidateQueries(["employees_by_branch", branch_id])
				onClose()
			},
			onError: () => {
				toast({
					title: "Chuyển nhân viên thất bại",
					status: "error"
				})
			}
		}
	)

	const [searchText, setSearchText] = useState("")

	const handleSelect = (employeeId: number) => {
		const employee = employeesData.find(e => e.id === employeeId)
		if (employee?.roles.length === 0) {
			toast({
				title: "Nhân viên phải có ít nhất một quyền",
				status: "error"
			})
			return
		}
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
		if (employees.length === 0) {
			toast({
				title: "Phải chọn ít nhất một nhân viên",
				status: "error"
			})
			return
		}
		mutate()
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
					<Button onClick={handleSubmit} isLoading={isTransferringEmployees}>
						{"Xác nhận"}
					</Button>
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
