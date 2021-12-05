import { LoginInput } from "@@types"
import { login, loginAsAdmin } from "@api"
import { useFormCore } from "@hooks"
import { useStoreActions } from "@store"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import { useMutation } from "react-query"
import fetcher from "src/api/fetcher"

const useLogin = (admin: boolean) => {
	const setInfo = useStoreActions(a => a.setInfo)

	const router = useRouter()

	const [generalError, setGeneralError] = useState("")

	const { values, setValue, errors, setError } = useFormCore<LoginInput>({
		email: "",
		password: "",
	})

	useEffect(() => {
		setGeneralError("")
	}, [values])

	const validate = () => {
		let isSubmittable = true

		// Check required fields
		let valuesKeys = Object.keys(values) as Array<keyof LoginInput>
		valuesKeys.forEach(key => {
			if (!values[key]) {
				setError(key, `Required`)
				isSubmittable = false
			}
		})

		return isSubmittable
	}

	const { mutate: mutateLogin, isLoading: isLoadingLogin } = useMutation(
		() => fetcher.post("/auth/login", values).then(res => res.data),
		{
			onSuccess: data => {
				setInfo(data.info)
				router.push("/")
			},
			onError: (err: any) => {
				setGeneralError(err.response.data.errors)
			},
			onSettled: data => console.log(data),
		}
	)

	const { mutate: mutateLoginAsAdmin, isLoading: isLoadingLoginAdmin } = useMutation(() => loginAsAdmin(values), {
		onSuccess: data => {
			setInfo(data.info)
			router.push("/admin")
		},
		onError: (err: any) => {
			setGeneralError(err.response.data.errors)
		},
	})

	const handleLogin = () => {
		if (validate()) {
			admin ? mutateLoginAsAdmin() : mutateLogin()
		}
	}

	return {
		isLoading: admin ? isLoadingLoginAdmin : isLoadingLogin,
		handleLogin,
		values,
		setValue,
		errors,
		validate,
		generalError,
	}
}
export default useLogin
