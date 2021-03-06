import { getGuard } from "@api"
import { Flex, Box, Button, Text, chakra, Heading } from "@chakra-ui/react"
import { TextControl } from "@components/shared"
import { useRouter } from "next/router"
import { useState } from "react"
import { BsFillPersonFill } from "react-icons/bs"
import { FaStore } from "react-icons/fa"
import { useQuery } from "react-query"
import LoginModeSelector, { LoginMode } from "./LoginModeSelector"
import useLogin from "./useLogin"

export const LoginUI = () => {
	const router = useRouter()

	const [currentMode, setCurrentMode] = useState<LoginMode>("employee")

	const { isLoading, errors, handleSubmit, register, generalError } = useLogin(currentMode === "owner")

	useQuery("get-guard", () => getGuard(), {
		onSuccess: role => {
			if (role === "store") router.push("/admin")
			else if (role === "employee") router.push("/main")
		}
	})

	return (
		<Box>
			<LoginModeSelector currentMode={currentMode} setCurrentMode={setCurrentMode} />
			<chakra.form onSubmit={handleSubmit}>
				<Heading fontWeight="semibold" color={"fill.primary"} fontSize="xl" mb={4}>
					ĐĂNG NHẬP
				</Heading>
				<TextControl label="Email" {...register("email")} error={errors?.email?.message} />
				<TextControl label="Mật khẩu" {...register("password")} error={errors?.password?.message} type="password" />
				<Text fontSize="sm" w="full" textAlign="center" color={"fill.danger"} mb={4}>
					{generalError}
				</Text>
				<Button w="full" type="submit" isLoading={isLoading} mb={4} colorScheme="blue">
					{"Đăng Nhập"}
				</Button>
			</chakra.form>
			<Box h="1px" bg={"border.primary"} w="full" mb={2} />
			<Text fontSize="sm" color={"fill.primary"} cursor="pointer" onClick={() => router.push("/register")} fontWeight="black">
				Tạo cửa hàng
			</Text>
		</Box>
	)
}

export default LoginUI
