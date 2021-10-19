import { Heading, Flex, Box, Button, chakra } from "@chakra-ui/react"
import { DateControl, RadioControl, TextControl } from "@components/shared"
import { format } from "date-fns"
import useRegister from "./useRegister"

export const RegisterUI = () => {
	const { values, setValue, errors, isLoading, mutateRegister } = useRegister()
	const {
		name,
		email,
		phone,
		gender,
		date_of_birth,
		store_name,
		branch_address,
		username,
		password,
		confirmPassword,
	} = values

	return (
		<Flex direction="column" h="full" bg="gray.50">
			<Box p={4} shadow="base" bg="blue.500" color="white">
				<Heading>{"Đăng Ký"}</Heading>
			</Box>
			<Box flex={1} w="full" overflow="auto" p={4}>
				<Flex justify="center" w="full">
					<chakra.form w="full" maxW="40rem" onSubmit={(e) => e.preventDefault()}>
						<Heading size="small" mb={2}>
							{"Thông tin cá nhân"}
						</Heading>
						<Box p={4} rounded="md" shadow="base" mb={4} bg="white">
							<TextControl
								label="Họ tên"
								value={name}
								onChange={(v) => setValue("name", v)}
								error={errors.name}
							/>
							<TextControl
								label="Email"
								value={email}
								onChange={(v) => setValue("email", v)}
								error={errors.email}
							/>
							<TextControl
								label="Điện thoại"
								value={phone}
								onChange={(v) => setValue("phone", v)}
								error={errors.phone}
								type="tel"
							/>
							<RadioControl
								label="Giới tính"
								value={gender}
								onChange={(v) => setValue("gender", v)}
								data={[
									{ value: "male", text: "Nam" },
									{ value: "female", text: "Nữ" },
								]}
								error={errors.gender}
							/>
							<DateControl
								label="Ngày sinh"
								value={date_of_birth}
								onChange={(v) => setValue("date_of_birth", format(new Date(v), "yyyy-MM-dd"))}
								error={errors.date_of_birth}
							/>
						</Box>
						<Heading size="small" mb={2}>
							{"Thông tin cửa hàng"}
						</Heading>
						<Box p={4} rounded="md" shadow="base" mb={4} bg="white">
							<TextControl
								label="Tên cửa hàng"
								value={store_name}
								onChange={(v) => {
									setValue("store_name", v)
									setValue("branch_name", v)
								}}
								error={errors.store_name}
							/>
							<TextControl
								label="Địa chỉ cửa hàng"
								value={branch_address}
								onChange={(v) => setValue("branch_address", v)}
								error={errors.branch_address}
							/>
						</Box>
						<Heading size="small" mb={2}>
							{"Thông tin đăng nhập"}
						</Heading>
						<Box p={4} rounded="md" shadow="base" mb={4} bg="white">
							<TextControl
								label="Tên đăng nhập"
								value={username}
								onChange={(v) => setValue("username", v)}
								error={errors.username}
							/>
							<TextControl
								label="Mật khẩu"
								value={password}
								onChange={(v) => setValue("password", v)}
								error={errors.password}
								type="password"
							/>
							<TextControl
								label="Nhập lại mật khẩu"
								value={confirmPassword}
								onChange={(v) => setValue("confirmPassword", v)}
								type="password"
								error={errors.confirmPassword}
							/>
						</Box>
						<Button w="full" onClick={mutateRegister} isLoading={isLoading} type="submit">
							{"Đăng Ký"}
						</Button>
					</chakra.form>
				</Flex>
			</Box>
		</Flex>
	)
}

export default RegisterUI
