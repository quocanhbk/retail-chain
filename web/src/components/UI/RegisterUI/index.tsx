import { RegisterStoreInput, client, Store } from "@api"
import { Heading, Box, Button, Text, chakra, Checkbox } from "@chakra-ui/react"
import { TextControl } from "@components/shared"
import { yupResolver } from "@hookform/resolvers/yup"
import { useStoreActions } from "@store"
import { useRouter } from "next/router"
import { useState } from "react"
import { useForm } from "react-hook-form"
import { useMutation } from "react-query"
import * as Yup from "yup"

const validationSchema = Yup.object().shape({
	name: Yup.string().required("Name is required"),
	email: Yup.string().email("Email is invalid").required("Email is required"),
	password: Yup.string().required("Password is required"),
	password_confirmation: Yup.string()
		.required("Password confirmation is required")
		.oneOf([Yup.ref("password"), null], "Password must match"),
	remember: Yup.boolean()
})

export const RegisterUI = () => {
	const router = useRouter()

	const setInfo = useStoreActions(s => s.setStoreInfo)

	const [generalError, setGeneralError] = useState("")

	const {
		register,
		handleSubmit,
		formState: { errors },
		watch
	} = useForm<RegisterStoreInput>({
		resolver: yupResolver(validationSchema)
	})

	watch(() => setGeneralError(""))

	const { mutate: mutateRegisterStore, isLoading: isRegistering } = useMutation<Store, Error, RegisterStoreInput>(
		input => client.store.registerStore(input).then(res => res.data),
		{
			onSuccess: data => {
				setInfo(data)
				router.push("/admin")
			},
			onError: error => {
				setGeneralError(error.message)
			}
		}
	)

	const handleRegister = handleSubmit(input => mutateRegisterStore(input))

	return (
		<chakra.form onSubmit={handleRegister}>
			<Heading fontWeight="bold" color="telegram.500" fontSize="xl" borderBottom={"1px"} borderColor="border.primary" mb={4} pb={2}>
				ĐĂNG KÝ
			</Heading>
			<TextControl label="Tên cửa hàng" {...register("name")} error={errors.name?.message} />
			<TextControl label="Email" {...register("email")} error={errors.email?.message} />
			<TextControl label="Mật khẩu" {...register("password")} error={errors.password?.message} type="password" />
			<TextControl
				label="Nhập lại mật khẩu"
				{...register("password_confirmation")}
				error={errors.password_confirmation?.message}
				type="password"
			/>
			{generalError && (
				<Text fontSize="sm" w="full" textAlign="center" color={"fill.danger"} mb={4} bg="red.50" py={1} rounded="md">
					{generalError}
				</Text>
			)}
			<Checkbox {...register("remember")} mb={4}>
				{"Nhớ mật khẩu"}
			</Checkbox>
			<Button w="full" colorScheme={"telegram"} type="submit" isLoading={isRegistering} mb={4}>
				Đăng ký
			</Button>
			<Box h="1px" bg="gray.300" w="full" mb={2} />
			<Text fontSize="sm" color="telegram.600" cursor="pointer" onClick={() => router.push("/login")} fontWeight="black">
				Đăng nhập
			</Text>
		</chakra.form>
	)
}

export default RegisterUI
