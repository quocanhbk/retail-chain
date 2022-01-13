import { Box, Button, chakra, Stack } from "@chakra-ui/react"
import { MultipleSelectControl } from "@components/module"
import { DateInputControl } from "@components/module/DateInput.tsx"
import RadioInputControl from "@components/module/RadioInput.tsx/RadioInputControl"
import { SelectControl } from "@components/module/Select"
import { BackableTitle, TextControl } from "@components/shared"
import { employeeRoles, genders } from "@constants"
import AvatarInput from "./AvatarInput"
import useCreateEmployee from "./useCreateEmployee"

const CreateEmployeeUI = () => {
	const { branches, values, setValue, errors, handleSubmit, isLoading } = useCreateEmployee()

	return (
		<Box p={4}>
			<BackableTitle backPath="/admin/manage/employee" text="Tạo nhân viên" />
			<chakra.form onSubmit={handleSubmit}>
				<AvatarInput file={values.avatar} onSubmit={f => setValue("avatar", f)} />
				<Stack direction={["column", "row"]} spacing={8}>
					<Box w="full" maxW="24rem">
						<SelectControl
							label="Chi nhánh làm việc"
							selected={
								branches!.map(b => ({ ...b, value: b.name })).find(b => b.id === values.branch_id) ||
								null
							}
							selections={branches!.map(b => ({ ...b, value: b.name }))}
							onChange={newBranch => setValue("branch_id", newBranch ? newBranch.id : null)}
						/>

						<TextControl
							label="Tên nhân viên"
							value={values.name}
							onChange={newValue => setValue("name", newValue)}
							error={errors.name}
						/>
						<TextControl
							label="Email"
							value={values.email}
							onChange={newValue => setValue("email", newValue)}
							error={errors.email}
						/>
						<TextControl
							label="Mật khẩu"
							value={values.password}
							onChange={newValue => setValue("password", newValue)}
							type="password"
							error={errors.password}
						/>
						<TextControl
							label="Xác nhận mật khẩu"
							value={values.password_confirmation}
							onChange={newValue => setValue("password_confirmation", newValue)}
							type="password"
							error={errors.password_confirmation}
						/>
					</Box>
					<Box w="full" maxW="24rem">
						<MultipleSelectControl
							label="Quyền"
							selections={employeeRoles}
							selected={employeeRoles.filter(role => values.roles.includes(role.id))}
							onChange={newRoles =>
								setValue(
									"roles",
									newRoles.map(role => role.id)
								)
							}
							error={errors.roles}
						/>
						<TextControl
							label="Số điện thoại"
							value={values.phone}
							onChange={newValue => setValue("phone", newValue)}
							error={errors.phone}
						/>
						<DateInputControl
							label="Ngày sinh"
							value={values.birthday}
							onChange={newValue => setValue("birthday", newValue)}
							error={errors.birthday}
						/>
						<RadioInputControl
							label="Giới tính"
							data={genders}
							value={values.gender || "unknown"}
							onChange={g => setValue("gender", g)}
						/>
						<Button type="submit" isLoading={isLoading}>
							{"Xác nhận"}
						</Button>
					</Box>
				</Stack>
			</chakra.form>
		</Box>
	)
}

export default CreateEmployeeUI
