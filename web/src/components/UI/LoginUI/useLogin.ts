import { LoginInput, login, loginAdmin } from "@api"
import { useFormCore } from "@hooks"
import useStore from "@store"
import { useRouter } from "next/router"
import { useState } from "react"
import { useMutation } from "react-query"

const useRegister = (admin: boolean) => {
	const setInfo = useStore(s => s.setInfo)
	const router = useRouter()
	const [generalError, setGeneralError] = useState("")
	const { values, setValue, errors, setError } = useFormCore<LoginInput>({
		email: "",
		password: "",
	})

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

	const { mutate: mutateLogin, isLoading: isLoadingLogin } = useMutation(() => login(values), {
		onSuccess: data => {
			setInfo(data.info)
			router.push("/")
		},
		onError: (err: any) => {
			setGeneralError(err.reponse.errors)
		},
	})

	const { mutate: mutateLoginAdmin, isLoading: isLoadingLoginAdmin } = useMutation(() => loginAdmin(values), {
		onSuccess: data => {
			setInfo(data.info)
			router.push("/admin")
		},
		onError: (err: any) => {
			setGeneralError(err.reponse.errors)
		},
	})

	const handleLogin = () => {
		if (validate()) {
			admin ? mutateLoginAdmin() : mutateLogin()
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
export default useRegister
