import { RegisterInput } from "@@types"
import { register } from "@api"
import { isEmail } from "@helper"
import { useFormCore } from "@hooks"
import { useStoreActions } from "@store"
import router, { useRouter } from "next/router"
import { useMutation } from "react-query"

interface RegisterFormInput extends RegisterInput {
	confirmPassword: ""
}

const useRegister = () => {
	const setInfo = useStoreActions(a => a.setInfo)
	const { values, setValue, errors, setError, initError } = useFormCore<RegisterFormInput>({
		name: "",
		email: "",
		password: "",
		confirmPassword: "",
		store_name: "",
	})

	const validate = () => {
		let isSubmittable = true

		// Check required fields
		let valuesKeys = Object.keys(values) as Array<keyof RegisterInput>
		valuesKeys.forEach(key => {
			if (!values[key]) {
				setError(key, `Required`)
				isSubmittable = false
			}
		})

		// Check if email is valid
		if (values.email && !isEmail(values.email)) {
			setError("email", "Email is invalid")
		}

		// Check if password is confirmed correctly
		if (values.password && values.confirmPassword && values.password !== values.confirmPassword) {
			setError("confirmPassword", "Password is not match")
		}

		return isSubmittable
	}

	const { mutate, isLoading } = useMutation(() => register(values), {
		onSuccess: data => {
			setInfo(data.info)
			router.push("/admin")
		},
		onError: (err: any) => {
			initError({
				...errors,
				...Object.fromEntries(Object.keys(err.errors).map(errorKey => [errorKey, err.errors[errorKey][0]])),
			})
		},
	})

	const mutateRegister = () => {
		if (validate()) {
			mutate()
		}
	}

	return {
		isLoading,
		values,
		setValue,
		errors,
		mutateRegister,
	}
}
export default useRegister
