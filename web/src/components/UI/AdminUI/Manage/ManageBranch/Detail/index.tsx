import { Box, Button, chakra, Flex, Stack, Text } from "@chakra-ui/react"
import { BackableTitle, FormControl, ModeInput } from "@components/shared"
import ImageInput from "./ImageInput"
import useBranchDetail from "./useBranchDetail"
import CreateEmployeeModal from "./CreateEmployeeModal"
import AddEmployeeButton from "./AddEmployeeButton"
import EmployeesTable from "./EmployeesTable"
import TransferEmployeeModal from "./TransferEmployeeModal"
import { getBranchImage } from "@api"

interface BranchDetailUIProps {
	id: number
}

const BranchDetailUI = ({ id }: BranchDetailUIProps) => {
	const { branch, isLoading, employees, setIsAddingEmployee, values, setValue, handleUpdateBranch, handleUpdateImage, isAddingEmployee } =
		useBranchDetail(id)

	if (isLoading) {
		return <Text>{"Loading..."}</Text>
	}

	if (!branch || !employees) {
		return <Text>{"Không tìm thấy chi nhánh"}</Text>
	}

	return (
		<Box p={4}>
			<Box w="full">
				<BackableTitle text={"Tạo chi nhánh"} backPath="/admin/manage/branch" mb={4} />

				<chakra.form>
					<Stack direction="row" spacing={4} w="full" mb={4}>
						<Box flex={1}>
							<ImageInput file={getBranchImage(branch?.image_key) ?? "/images/store.jpg"} onSubmit={handleUpdateImage} />
						</Box>
						<Box flex={1}>
							<Stack direction="column" spacing={2} w="full">
								<FormControl label="Tên" isRequired={true}>
									<ModeInput
										value={values.name}
										onChange={e => setValue("name", e.target.value)}
										onSave={handleUpdateBranch}
									/>
								</FormControl>

								<FormControl label="Địa chỉ" isRequired={true}>
									<ModeInput
										value={values.address}
										onChange={e => setValue("address", e.target.value)}
										onSave={handleUpdateBranch}
									/>
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
					<EmployeesTable employees={employees} />
				</chakra.form>
			</Box>
			<CreateEmployeeModal isOpen={isAddingEmployee === "create"} onClose={() => setIsAddingEmployee(null)} branch_id={id} />
			<TransferEmployeeModal isOpen={isAddingEmployee === "transfer"} onClose={() => setIsAddingEmployee(null)} branch_id={id} />
		</Box>
	)
}

export default BranchDetailUI
