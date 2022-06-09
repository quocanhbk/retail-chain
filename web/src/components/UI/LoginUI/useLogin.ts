import { loginEmployee, LoginInput, loginStore } from "@api"
import { useStoreActions } from "@store"
import { useRouter } from "next/router"
import { useForm } from "react-hook-form"
import { useMutation } from "react-query"
import * as Yup from "yup"
import { yupResolver } from "@hookform/resolvers/yup"
import { useState } from "react"

const validationSchema = Yup.object().shape({
	email: Yup.string().required("Email bắt buộc").email("Email không hợp lệ"),
	password: Yup.string().required("Mật khẩu bắt buộc").min(6, "Mật khẩu phải có ít nhất 6 ký tự"),
	remember: Yup.boolean()
})

const useLogin = (admin: boolean) => {
	const setStoreInfo = useStoreActions(s => s.setStoreInfo)
	const setEmployeeInfo = useStoreActions(s => s.setEmployeeInfo)

	const [generalError, setGeneralError] = useState("")

	const {
		register,
		handleSubmit: handleSubmitForm,
		formState: { errors },
		watch
	} = useForm<LoginInput>({ resolver: yupResolver(validationSchema) })

	watch(() => setGeneralError(""))

	const router = useRouter()

	const { mutate: mutateLoginEmployee, isLoading: isLoadingLoginEmployee } = useMutation(loginEmployee, {
		onSuccess: data => {
			setEmployeeInfo(data)
			router.push("/")
		},
		onError: (err: Error) => {
			setGeneralError(err.message)
		}
	})

	const { mutate: mutateLoginStore, isLoading: isLoadingLoginStore } = useMutation(loginStore, {
		onSuccess: data => {
			setStoreInfo(data)
			router.push("/admin")
		},
		onError: (err: Error) => {
			setGeneralError(err.message)
		}
	})

	const isLoading = admin ? isLoadingLoginStore : isLoadingLoginEmployee

	const handleSubmit = handleSubmitForm(values => (admin ? mutateLoginStore(values) : mutateLoginEmployee(values)))

	return {
		isLoading,
		errors,
		register,
		handleSubmit,
		generalError
	}
}
export default useLogin
