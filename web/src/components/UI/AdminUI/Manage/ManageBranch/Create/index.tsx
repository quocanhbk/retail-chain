import { Box, Button, chakra, Flex, Input, Stack, Text } from "@chakra-ui/react"
import { BackableTitle, FormControl, SubmitConfirmAlert } from "@components/shared"
import ImageInput from "./ImageInput"
import useCreateBranch from "./useCreateBranch"
import CreateEmployeeModal from "./CreateEmployeeModal"
import AddEmployeeButton from "./AddEmployeeButton"
import EmployeesTable from "./EmployeesTable"
import TransferEmployeeModal from "./TransferEmployeeModal"
import { TransferEmployee } from "@api"

const CreateBranchUI = () => {
	const {
		values,
		setValue,
		handleSubmit,
		addEmployee,
		removeEmployee,
		isAddingEmployee,
		setIsAddingEmployee,
		transferEmployees,
		confirmCreate,
		setConfirmCreate,
		createBranch,
		isCreatingBranch
	} = useCreateBranch()

	return (
		<Box p={4}>
			<Box w="full">
				<BackableTitle text={"Tạo chi nhánh"} backPath="/admin/manage/branch" mb={4} />

				<chakra.form onSubmit={handleSubmit}>
					<Stack direction="row" spacing={4} w="full" mb={4}>
						<Box flex={1}>
							<ImageInput file={values.image ?? "/images/store.jpg"} onSubmit={f => setValue("image", f)} />
						</Box>
						<Box flex={1}>
							<Stack direction="column" spacing={2} w="full">
								<FormControl label="Tên" isRequired={true}>
									<Input value={values.name} onChange={e => setValue("name", e.target.value)} />
								</FormControl>

								<FormControl label="Địa chỉ" isRequired={true}>
									<Input value={values.address} onChange={e => setValue("address", e.target.value)} />
								</FormControl>
							</Stack>
						</Box>
					</Stack>
					<Flex align="center" mb={2} w="full" justify="space-between">
						<Text fontSize={"xl"} fontWeight={500}>
							{"Danh sách nhân viên"}
						</Text>
						<AddEmployeeButton setIsCreatingEmployee={setIsAddingEmployee} />
					</Flex>
					<EmployeesTable employees={values.adding_employees} onRemove={removeEmployee} />
					<Flex w="full" align="center" justify="flex-end" mt={6}>
						<Button type="submit" w="6rem">
							{"Xác nhận"}
						</Button>
					</Flex>
				</chakra.form>
			</Box>
			<CreateEmployeeModal isOpen={isAddingEmployee === "create"} onClose={() => setIsAddingEmployee(null)} onSubmit={addEmployee} />
			<TransferEmployeeModal
				isOpen={isAddingEmployee === "transfer"}
				onClose={() => setIsAddingEmployee(null)}
				onSubmit={transferEmployees}
				selectedEmployeeIds={(values.adding_employees.filter(e => e.type === "transfer") as TransferEmployee[]).map(e => e.id)}
			/>
			<SubmitConfirmAlert
				isOpen={confirmCreate}
				onClose={() => setConfirmCreate(false)}
				onConfirm={createBranch}
				title="Xác nhận tạo chi nhánh"
				isLoading={isCreatingBranch}
			>
				<Text>Bạn có chắc chắn muốn tạo chi nhánh này?</Text>
			</SubmitConfirmAlert>
		</Box>
	)
}

export default CreateBranchUI
