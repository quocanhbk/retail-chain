import { Flex, Box, Button, Text, Link, Grid, chakra, Heading } from "@chakra-ui/react"
import { FormBox, TextControl } from "@components/shared"
import useLogin from "./useLogin"

interface LoginUIProps {
	admin?: boolean
}

export const LoginUI = ({ admin = false }: LoginUIProps) => {
	const { values, setValue, errors, isLoading, handleLogin, generalError } = useLogin(admin)
	const { email, password } = values

	return (
		<Flex h="full" direction={["column", "column", "row"]} overflow="auto" align={["center", "center", "stretch"]}>
			<Grid placeItems="center" flex={2} bgGradient="linear(to-r, telegram.800, telegram.600)" p={8}>
				<Text fontSize={["3rem", "3rem", "7rem", "7rem"]} fontWeight="black" color="white" fontFamily="Brandon">
					BKRM RETAIL MANAGEMENT SYSTEM
				</Text>
			</Grid>
			<Flex direction="column" justify="center" w="24rem" p={8}>
				<chakra.form onSubmit={e => e.preventDefault()}>
					<Heading fontWeight="semibold" color="telegram.500">
						ĐĂNG NHẬP {admin ? "ADMIN" : ""}
					</Heading>
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
					<Text>{generalError}</Text>
					<Button w="full" onClick={handleLogin} isLoading={isLoading} type="submit">
						{"Đăng Nhập"}
					</Button>
				</chakra.form>
				<Box w="full" bg="gray.100" my={2} />
			</Flex>
		</Flex>
	)
}

export default LoginUI
