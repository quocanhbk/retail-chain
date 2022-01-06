import { getGuard } from "@api"
import { Flex, Box, Button, Text, Grid, chakra, Heading } from "@chakra-ui/react"
import { TextControl } from "@components/shared"
import { useRouter } from "next/router"
import { useState } from "react"
import { useQuery } from "react-query"
import useLogin from "./useLogin"

const loginMode = [
	{ id: "employee", text: "Nhân viên" },
	{ id: "owner", text: "Chủ cửa hàng" },
] as const

type LoginMode = typeof loginMode[number]["id"]

export const LoginUI = () => {
	const router = useRouter()

	const [currentMode, setCurrentMode] = useState<LoginMode>("employee")

	const { values, setValue, errors, isLoading, handleLogin, generalError } = useLogin(currentMode === "owner")

	const { email, password } = values

	useQuery("get-guard", () => getGuard(), {
		onSuccess: role => {
			if (role === "store") router.push("/admin")
			else if (role === "employee") router.push("/")
		},
	})

	return (
		<Flex h="full" direction={["column", "column", "row"]} overflow="auto" align={["center", "center", "stretch"]}>
			<Grid placeItems="center" flex={[1, 2]} bgColor="gray.600" p={8} display={["none", "none", "flex"]}>
				<Text fontSize={["4rem", "5rem", "6rem", "7rem"]} fontWeight="black" color="white" fontFamily="Brandon">
					BKRM RETAIL MANAGEMENT SYSTEM
				</Text>
			</Grid>
			<Flex direction="column" justify="center" w="24rem" p={8} h="full">
				<Flex shadow="base" overflow="hidden" mb={4} rounded="md">
					{loginMode.map(mode => (
						<Box
							key={mode.id}
							flex={1}
							p={2}
							textAlign="center"
							cursor="pointer"
							bg={currentMode === mode.id ? "gray.500" : "transparent"}
							color={currentMode === mode.id ? "white" : "black"}
							fontWeight={currentMode === mode.id ? "bold" : "normal"}
							onClick={() => setCurrentMode(mode.id)}
						>
							{mode.text}
						</Box>
					))}
				</Flex>
				<chakra.form onSubmit={e => e.preventDefault()}>
					<Heading fontWeight="semibold" color="gray.500" fontSize="xl" mb={4}>
						ĐĂNG NHẬP
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
					<Text fontSize="sm" w="full" textAlign="center" color="gray.500" mb={2}>
						{generalError}
					</Text>
					<Button w="full" onClick={() => handleLogin()} isLoading={isLoading} type="submit" mb={4}>
						{"Đăng Nhập"}
					</Button>
					<Box h="1px" bg="gray.300" w="full" mb={2} />
					<Text
						fontSize="sm"
						color="gray.600"
						cursor="pointer"
						onClick={() => router.push("/register")}
						fontWeight="black"
					>
						Tạo cửa hàng
					</Text>
				</chakra.form>
			</Flex>
		</Flex>
	)
}

export default LoginUI
