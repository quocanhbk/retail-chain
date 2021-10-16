import { login, LoginInput } from "@api"
import { useFormCore } from "@hooks"
import { useMutation } from "react-query"

const useRegister = () => {
	const { values, setValue, errors, setError } = useFormCore<LoginInput>({
		username: "",
		password: "",
	})

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
			console.log(data)
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
