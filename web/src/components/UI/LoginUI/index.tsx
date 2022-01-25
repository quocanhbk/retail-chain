import { getGuard } from "@api"
import { Flex, Box, Button, Text, chakra, Heading } from "@chakra-ui/react"
import { TextControl } from "@components/shared"
import { useTheme } from "@hooks"
import { useRouter } from "next/router"
import { useState } from "react"
import { useQuery } from "react-query"
import useLogin from "./useLogin"
const loginMode = [
	{ id: "employee", text: "Nhân viên" },
	{ id: "owner", text: "Chủ cửa hàng" }
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
			else if (role === "employee") router.push("/main")
		}
	})

	const { fillPrimary, fillDanger, textSecondary } = useTheme()

	return (
		<Box>
			<Flex overflow="hidden" mb={8} pos="relative" shadow="base">
				{loginMode.map(mode => (
					<Box
						key={mode.id}
						flex={1}
						p={2}
						textAlign="center"
						cursor="pointer"
						fontWeight={currentMode === mode.id ? "bold" : "normal"}
						onClick={() => setCurrentMode(mode.id)}
					>
						{mode.text}
					</Box>
				))}
				<Box
					pos="absolute"
					bottom={0}
					height={"4px"}
					w="50%"
					bg={fillPrimary}
					left={currentMode === "employee" ? "0%" : "50%"}
					transition="all 0.25s ease-in-out"
				/>
			</Flex>
			<chakra.form onSubmit={e => e.preventDefault()}>
				<Heading fontWeight="semibold" color={fillPrimary} fontSize="xl" mb={4}>
					ĐĂNG NHẬP
				</Heading>
				<TextControl label="Email" value={email} onChange={v => setValue("email", v)} error={errors.email} />
				<TextControl
					label="Mật khẩu"
					value={password}
					onChange={v => setValue("password", v)}
					error={errors.password}
					type="password"
				/>
				<Text fontSize="sm" w="full" textAlign="center" color={fillDanger} mb={2} h="1.2rem">
					{generalError}
				</Text>
				<Button w="full" onClick={() => handleLogin()} isLoading={isLoading} type="submit" mb={4}>
					{"Đăng Nhập"}
				</Button>
				<Box h="1px" bg={textSecondary} w="full" mb={2} />
				<Text fontSize="sm" color={fillPrimary} cursor="pointer" onClick={() => router.push("/register")} fontWeight="black">
					Tạo cửa hàng
				</Text>
			</chakra.form>
		</Box>
	)
}

export default LoginUI
