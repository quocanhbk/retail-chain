import { Heading, Flex, Box, Button, Text } from "@chakra-ui/react"
import { TextControl } from "@components/shared"
import { useRouter } from "next/router"
import useLogin from "./useLogin"

export const LoginUI = () => {
	const { values, setValue, errors, isLoading, mutateRegister } = useLogin()
	const { username, password } = values
	const router = useRouter()
	return (
		<Flex direction="column" h="full" bg="gray.50">
			<Box p={4} shadow="base" bg="blue.500" color="white">
				<Heading>{"Đăng Nhập"}</Heading>
			</Box>
			<Box flex={1} w="full" overflow="auto" p={4}>
				<Flex justify="center" align="center" w="full" h="full">
					<Box w="full" maxW="20rem">
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
							<Button w="full" onClick={mutateRegister} isLoading={isLoading}>
								{"Đăng Nhập"}
							</Button>
							<Box w="full" h="1px" bg="gray.200" my={4} />
							<Flex justify="space-between" align="center">
								<Text w="full" textAlign="center" size="small">
									{"Chưa có tài khoản?"}
								</Text>
								<Button
									w="full"
									onClick={() => router.push("/register")}
									variant="outline"
									size="sm"
									isLoading={isLoading}
								>
									{"Đăng Ký"}
								</Button>
							</Flex>
						</Box>
					</Box>
				</Flex>
			</Box>
		</Flex>
	)
}

export default LoginUI
