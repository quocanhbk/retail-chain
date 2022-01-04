import { Heading, Flex, Box, Button, HStack, Grid, Text, chakra } from "@chakra-ui/react"
import { DateControl, FormBox, RadioControl, TextControl } from "@components/shared"
import { format } from "date-fns"
import { useRouter } from "next/router"
import useRegister from "./useRegister"

export const RegisterUI = () => {
	const { values, setValue, errors, mutateRegister, isLoading } = useRegister()

	const { name, email, password, password_confirmation, remember } = values

	const router = useRouter()

	return (
		<Flex h="full" overflow="auto">
			<Grid placeItems="center" flex={2} bgColor="telegram.600" p={8} display={["none", "none", "block"]}>
				<Text fontSize="7rem" fontWeight="black" color="white" fontFamily="Brandon">
					BKRM RETAIL MANAGEMENT SYSTEM
				</Text>
			</Grid>
			<Flex direction="column" justify="center" w="24rem" p={8}>
				<chakra.form onSubmit={e => e.preventDefault()}>
					<Heading fontWeight="semibold" color="telegram.500" fontSize="xl">
						ĐĂNG KÝ
					</Heading>
					<TextControl
						label="Tên cửa hàng"
						value={name}
						onChange={v => setValue("name", v)}
						error={errors.name}
					/>
					<TextControl
						label="Email"
						value={email}
						onChange={v => setValue("email", v)}
						error={errors.email}
					/>
					<TextControl
						label="Mật khẩu"
						value={password}
						onChange={v => setValue("password", v)}
						error={errors.password}
						type="password"
					/>
					<TextControl
						label="Nhập lại mật khẩu"
						value={password_confirmation}
						onChange={v => setValue("password_confirmation", v)}
						error={errors.password_confirmation}
						type="password"
					/>
					<Button
						w="full"
						colorScheme="gray"
						onClick={() => mutateRegister()}
						isLoading={isLoading}
						type="submit"
						mb={4}
					>
						Đăng ký
					</Button>
					<Box h="1px" bg="gray.300" w="full" mb={2} />
					<Text
						fontSize="sm"
						color="gray.600"
						cursor="pointer"
						onClick={() => router.push("/login")}
						fontWeight="black"
					>
						Đăng nhập
					</Text>
				</chakra.form>
			</Flex>
		</Flex>
	)
}

export default RegisterUI
