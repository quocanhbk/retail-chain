import { Heading, Flex, Box, Button, Text, Link, chakra } from "@chakra-ui/react"
import { TextControl } from "@components/shared"
import useLogin from "./useLogin"

export const LoginUI = () => {
	const { values, setValue, errors, isLoading, mutateRegister } = useLogin()
	const { username, password } = values
	return (
		<Flex direction="column" h="full" bg="gray.50">
			<Box p={4} shadow="base" bg="blue.500" color="white">
				<Heading>{"Đăng Nhập"}</Heading>
			</Box>
			<Box flex={1} w="full" overflow="auto" p={4}>
				<Flex justify="center" align="center" w="full" h="full">
					<Box w="full" maxW="20rem">
						<chakra.form
							p={4}
							rounded="md"
							shadow="base"
							mb={4}
							bg="white"
							onSubmit={(e) => e.preventDefault()}
						>
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
							<Button w="full" onClick={mutateRegister} isLoading={isLoading} type="submit">
								{"Đăng Nhập"}
							</Button>
							<Box w="full" h="1px" bg="gray.200" my={4} />
							<Flex justify="space-between" align="center">
								<Text size="small">{"Chưa có tài khoản?"}</Text>
								<Link href="/register">
									<Text size="small" fontWeight="semibold" color="blue.500">
										{"Đăng ký"}
									</Text>
								</Link>
							</Flex>
						</chakra.form>
					</Box>
				</Flex>
			</Box>
		</Flex>
	)
}

export default LoginUI
