import { login, LoginInput } from "@api"
import { useFormCore } from "@hooks"
import useStore from "@store"
import { useRouter } from "next/router"
import { useMutation } from "react-query"

const useRegister = () => {
	const authData = useStore((s) => s.authData)
	const initInfo = useStore((s) => s.initInfo)
	const { values, setValue, errors, setError } = useFormCore<LoginInput>(authData)
	const validate = () => {
		let isSubmittable = true

		// Check required fields
		let valuesKeys = Object.keys(values) as Array<keyof LoginInput>
		valuesKeys.forEach((key) => {
			if (!values[key]) {
				setError(key, `Required`)
				isSubmittable = false
			}
		})

		return isSubmittable
	}
	const { mutate, isLoading } = useMutation(() => login(values), {
		onSuccess: (data) => {
			if (data.state === "fail") {
				setError("username", data.errors)
			} else {
				console.log(data)
				initInfo(data)
			}
		},
	})

	const mutateLogin = () => {
		if (validate()) {
			mutate()
		}
	}

	return {
		isLoading,
		values,
		setValue,
		errors,
		validate,
		mutateRegister: mutateLogin,
	}
}
export default useRegister
