import { registerStore, RegisterStoreInput } from "@api"
import { isEmail } from "@helper"
import { useFormCore } from "@hooks"
import { useStoreActions } from "@store"
import { useRouter } from "next/router"
import { useMutation } from "react-query"

const useRegister = () => {
	const router = useRouter()
	const setInfo = useStoreActions(s => s.setInfo)

	const { values, setValue, errors, setError, initError } = useFormCore<RegisterStoreInput>({
		name: "",
		email: "",
		password: "",
		password_confirmation: "",
		remember: false,
	})

	const validate = () => {
		let isSubmittable = true

		// Check required fields
		const valuesKeys = Object.keys(values) as Array<keyof RegisterStoreInput>
		valuesKeys.forEach(key => {
			if (values[key] === "") {
				setError(key, `Required`)
				isSubmittable = false
			}
		})

		// Check if email is valid
		if (values.email && !isEmail(values.email)) {
			setError("email", "Email is invalid")
		}

		// Check if password is confirmed correctly
		if (values.password && values.password_confirmation && values.password !== values.password_confirmation) {
			setError("password_confirmation", "Password is not match")
			isSubmittable = false
		}

		return isSubmittable
	}

	const { mutate, isLoading } = useMutation(() => registerStore(values), {
		onSuccess: data => {
			setInfo(data)
			router.push("/admin")
		},
		onError: (err: any) => {
			console.log(err)
			// initError({
			// 	...errors,
			// 	...Object.fromEntries(Object.keys(err.errors).map(errorKey => [errorKey, err.errors[errorKey][0]])),
			// })
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
