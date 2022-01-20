import { createEmployee, CreateEmployeeInput, getEmployees } from "@api"
import { Box, Button, chakra, Checkbox, CheckboxGroup, HStack, Input, Radio, RadioGroup, Stack } from "@chakra-ui/react"
import { ChakraModal, DateInput, FormControl } from "@components/shared"
import { employeeRoles, genders } from "@constants"
import { useChakraToast, useFormCore, useTheme } from "@hooks"
import { useEffect, useRef } from "react"
import { useMutation, useQuery, useQueryClient } from "react-query"
import AvatarInput from "../../ManageEmployee/Create/AvatarInput"

interface CreateEmployeeModalProps {
	isOpen: boolean
	onClose: () => void
	branch_id: number
}

const CreateEmployeeModal = ({ isOpen, onClose, branch_id }: CreateEmployeeModalProps) => {
	const toast = useChakraToast()
	const qc = useQueryClient()
	const { values, setValue, initForm } = useFormCore<CreateEmployeeInput>({
		branch_id,
		name: "",
		email: "",
		password: "",
		password_confirmation: "",
		avatar: null,
		birthday: null,
		phone: "",
		roles: [],
		gender: ""
	})

	const { data: employees } = useQuery("employees", getEmployees, { initialData: [] })

	const { mutate, isLoading } = useMutation(() => createEmployee(values), {
		onSuccess: () => {
			toast({
				title: "Tạo nhân viên thành công",
				status: "success"
			})
			qc.invalidateQueries(["employees_by_branch", branch_id])
			onClose()
		},
		onError: () => {
			toast({
				title: "Tạo nhân viên thất bại",
				status: "error"
			})
		}
	})

	const validate = () => {
		if (!values.name) {
			toast({
				title: "Tên nhân viên là bắt buộc",
				status: "error"
			})
			return false
		}

		if (!values.email) {
			toast({
				title: "Email là bắt buộc",
				status: "error"
			})
			return false
		}

		if (!values.password) {
			toast({
				title: "Mật khẩu là bắt buộc",
				status: "error"
			})
			return false
		}

		if (values.password !== values.password_confirmation) {
			toast({
				title: "Mật khẩu không khớp",
				status: "error"
			})
			return false
		}

		if (values.roles.length === 0) {
			toast({
				title: "Vui lòng chọn ít nhất 1 quyền",
				status: "error"
			})
			return false
		}

		if (values.birthday !== null && Object.values(values.birthday).some(v => v === null)) {
			toast({
				title: "Ngày sinh không hợp lệ",
				status: "error"
			})
			return false
		}

		if (employees && employees.some(e => e.email === values.email)) {
			toast({
				title: "Email đã tồn tại",
				status: "error"
			})
			return false
		}

		return true
	}

	const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
		e.preventDefault()
		if (!validate()) {
			return
		}
		mutate()
	}

	const { borderPrimary } = useTheme()

	useEffect(() => {
		if (isOpen) initForm()
	}, [isOpen])

	const inputRef = useRef<HTMLInputElement>(null)

	return (
		<ChakraModal title="Tạo nhân viên" isOpen={isOpen} onClose={onClose} size="3xl" initialFocusRef={inputRef}>
			<chakra.form onSubmit={handleSubmit} noValidate>
				<AvatarInput file={values.avatar} onSubmit={f => setValue("avatar", f)} />
				<Stack direction={["column", "row"]} justify="space-between" spacing={6}>
					<Box w="full" maxW="24rem">
						<FormControl label="Tên nhân viên" mb={4} isRequired={true}>
							<Input value={values.name} onChange={e => setValue("name", e.target.value)} ref={inputRef} />
						</FormControl>

						<FormControl label="Email" mb={4} isRequired={true}>
							<Input
								value={values.email}
								onChange={e => setValue("email", e.target.value)}
								type="email"
								autoComplete="new-password"
							/>
						</FormControl>

						<FormControl label="Mật khẩu" mb={4} isRequired={true}>
							<Input
								value={values.password}
								onChange={e => setValue("password", e.target.value)}
								type="password"
								autoComplete="new-password"
							/>
						</FormControl>
						<FormControl label="Xác nhận mật khẩu" mb={4} isRequired={true}>
							<Input
								value={values.password_confirmation}
								onChange={e => setValue("password_confirmation", e.target.value)}
								type="password"
								autoComplete="new-password"
							/>
						</FormControl>
					</Box>
					<Box w="full" maxW="24rem">
						<FormControl label="Quyền" mb={4} isRequired={true}>
							<CheckboxGroup value={values.roles} onChange={value => setValue("roles", value)}>
								<HStack border="1px" borderColor={borderPrimary} px={4} h="2.5rem" rounded="md" justify="space-between">
									{employeeRoles.map(r => (
										<Checkbox key={r.id} value={r.id}>
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
								type="tel"
								autoComplete="new-password"
							/>
						</FormControl>
						<FormControl label="Ngày sinh" mb={4}>
							<DateInput value={values.birthday} onChange={value => setValue("birthday", value)} />
						</FormControl>
						<FormControl label="Giới tính" mb={4}>
							<RadioGroup value={values.gender || undefined} onChange={v => setValue("gender", v)}>
								<HStack border="1px" borderColor={borderPrimary} px={4} h="2.5rem" rounded="md" justify="space-between">
									{genders.map(gender => (
										<Radio key={gender.id} value={gender.id}>
											{gender.value}
										</Radio>
									))}
								</HStack>
							</RadioGroup>
						</FormControl>
						<HStack my={6} w="full" justify={"flex-end"} spacing={4}>
							<Button type="submit" isLoading={isLoading}>
								{"Xác nhận"}
							</Button>
							<Button variant="ghost" onClick={onClose} w="6rem" c>
								Hủy
							</Button>
						</HStack>
					</Box>
				</Stack>
			</chakra.form>
		</ChakraModal>
	)
}

export default CreateEmployeeModal
