import { loginEmployee, LoginEmployeeInput, loginStore } from "@api"
import { useChakraToast, useFormCore } from "@hooks"
import { useStoreActions } from "@store"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import { useMutation } from "react-query"

const useLogin = (admin: boolean) => {
	const setInfo = useStoreActions(s => s.setInfo)
	const toast = useChakraToast()
	const router = useRouter()

	const [generalError, setGeneralError] = useState("")

	const { values, setValue, errors, setError } = useFormCore<LoginEmployeeInput>({
		email: "",
		password: "",
		remember: false,
	})

	useEffect(() => {
		setGeneralError("")
	}, [values])

	const validate = () => {
		let isSubmittable = true

		// Check required fields
		const valuesKeys = Object.keys(values) as Array<keyof LoginEmployeeInput>
		valuesKeys.forEach(key => {
			if (values[key] === "") {
				setError(key, `Required`)
				isSubmittable = false
			}
		})
		return isSubmittable
	}

	const { mutate: mutateLoginEmployee, isLoading: isLoadingLoginEmployee } = useMutation(
		() => loginEmployee(values),
		{
			onSuccess: data => {
				setInfo(data)
				router.push("/")
			},
			onError: (err: any) => {
				console.log(err.response)
				toast({ title: "Error", message: err.response.data.message, status: "error" })
			},
		}
	)

	const { mutate: mutateLoginStore, isLoading: isLoadingLoginStore } = useMutation(() => loginStore(values), {
		onSuccess: data => {
			setInfo(data)
			router.push("/admin")
		},
		onError: (err: any) => {
			console.log(err.response)
			toast({ title: "Error", message: err.response.data.message, status: "error" })
		},
	})

	const handleLogin = () => {
		if (validate()) {
			admin ? mutateLoginStore() : mutateLoginEmployee()
		}
	}

	const isLoading = admin ? isLoadingLoginStore : isLoadingLoginEmployee

	return {
		isLoading,
		handleLogin,
		values,
		setValue,
		errors,
		validate,
		generalError,
	}
}
export default useLogin
