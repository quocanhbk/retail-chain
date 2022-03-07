import { Box, Button, chakra, Checkbox, CheckboxGroup, HStack, Input, Radio, RadioGroup, Stack, Text, VStack } from "@chakra-ui/react"
import { BackableTitle, DateInput, FormControl, Select } from "@components/shared"
import { employeeRoles, genders } from "@constants"
import { IoMdCheckmarkCircle, IoMdSettings } from "react-icons/io"
import AvatarInput from "./AvatarInput"
import DeleteEmployeePopup from "./DeleteEmployeePopup"
import useCreateEmployee from "./useCreateEmployee"

interface CreateEmployeeUIProps {
	id?: number
}

const CreateEmployeeUI = ({ id }: CreateEmployeeUIProps) => {
	const { branches, values, setValue, handleSubmit, isLoading, readOnly, setReadOnly, confirmDelete, setConfirmDelete, employee } =
		useCreateEmployee(id)

	return (
		<Box p={4} w="full" maxW="50rem">
			<BackableTitle backPath="/admin/manage/employee" text={id ? "Xem nhân viên" : "Tạo nhân viên"}>
				{id && (
					<VStack align="stretch" spacing={0}>
						<Text
							onClick={() => setReadOnly(!readOnly)}
							cursor="pointer"
							px={2}
							py={1}
							_hover={{ bg: "background.third" }}
							rounded="md"
						>
							{readOnly ? "Chỉnh sửa" : "Hủy chỉnh sửa"}
						</Text>
						<Text
							onClick={() => setConfirmDelete(true)}
							color={"fill.danger"}
							cursor="pointer"
							px={2}
							py={1}
							_hover={{ bg: "background.third" }}
							rounded="md"
						>
							{"Xóa"}
						</Text>
					</VStack>
				)}
			</BackableTitle>
			<chakra.form onSubmit={handleSubmit} noValidate>
				<AvatarInput file={values.avatar} onSubmit={f => setValue("avatar", f)} readOnly={readOnly} />
				<Stack direction={["column", "row"]} justify="space-between" spacing={8}>
					<Box w="full" maxW="24rem">
						<FormControl label="Chi nhánh làm việc" mb={4} isRequired={!readOnly}>
							<Select
								selected={branches!.map(b => ({ ...b, value: b.name })).find(b => b.id === values.branch_id) || null}
								selections={branches!.map(b => ({ ...b, value: b.name }))}
								onChange={newBranch => setValue("branch_id", newBranch ? newBranch.id : null)}
								readOnly={readOnly}
							/>
						</FormControl>
						<FormControl label="Tên nhân viên" mb={4} isRequired={!readOnly}>
							<Input value={values.name} onChange={e => setValue("name", e.target.value)} readOnly={readOnly} />
						</FormControl>

						<FormControl label="Email" mb={4} isRequired={!readOnly}>
							<Input
								value={values.email}
								onChange={e => setValue("email", e.target.value)}
								readOnly={readOnly}
								type="email"
								autoComplete="new-password"
							/>
						</FormControl>

						<FormControl label="Mật khẩu" mb={4} isRequired={!readOnly}>
							<Input
								value={id ? "********" : values.password}
								onChange={e => setValue("password", e.target.value)}
								readOnly={readOnly}
								type="password"
								autoComplete="new-password"
								isDisabled={!!id}
							/>
						</FormControl>
						<FormControl label="Xác nhận mật khẩu" mb={4} isRequired={!readOnly}>
							<Input
								value={id ? "********" : values.password_confirmation}
								onChange={e => setValue("password_confirmation", e.target.value)}
								readOnly={readOnly}
								type="password"
								autoComplete="new-password"
								isDisabled={!!id}
							/>
						</FormControl>
					</Box>
					<Box w="full" maxW="24rem">
						<FormControl label="Quyền" mb={4} isRequired={!readOnly}>
							<CheckboxGroup value={values.roles} onChange={value => setValue("roles", value)}>
								<HStack border="1px" borderColor={"border.primary"} px={4} h="2.5rem" rounded="md" justify="space-between">
									{employeeRoles.map(r => (
										<Checkbox key={r.id} value={r.id} isReadOnly={readOnly}>
											{r.value}
										</Checkbox>
									))}
								</HStack>
							</CheckboxGroup>
						</FormControl>
						<FormControl label="Số điện thoại" mb={4}>
							<Input
								value={values.phone}
								onChange={e => setValue("phone", e.target.value)}
								readOnly={readOnly}
								type="tel"
								autoComplete="new-password"
							/>
						</FormControl>
						<FormControl label="Ngày sinh" mb={4}>
							<DateInput value={values.birthday} onChange={value => setValue("birthday", value)} readOnly={readOnly} />
						</FormControl>
						<FormControl label="Giới tính" mb={4}>
							<RadioGroup value={values.gender || undefined} onChange={v => setValue("gender", v)}>
								<HStack border="1px" borderColor={"border.primary"} px={4} h="2.5rem" rounded="md" justify="space-between">
									{genders.map(gender => (
										<Radio key={gender.id} value={gender.id} isReadOnly={readOnly}>
											{gender.value}
										</Radio>
									))}
								</HStack>
							</RadioGroup>
						</FormControl>
					</Box>
				</Stack>
				<HStack borderTop={"1px"} borderColor={"border.primary"} pt={4}>
					<Button
						type="submit"
						isLoading={isLoading}
						w="8rem"
						colorScheme={readOnly ? "yellow" : "telegram"}
						leftIcon={readOnly ? <IoMdSettings /> : <IoMdCheckmarkCircle />}
					>
						{readOnly ? "Chỉnh sửa" : "Xác nhận"}
					</Button>
					{!readOnly && id && (
						<Button variant="ghost" onClick={() => setReadOnly(true)} w="8rem" colorScheme={"red"}>
							Hủy
						</Button>
					)}
					{}
				</HStack>
			</chakra.form>
			<DeleteEmployeePopup isOpen={confirmDelete} onClose={() => setConfirmDelete(false)} employeeId={id} employeeName={employee?.name} />
		</Box>
	)
}

export default CreateEmployeeUI
