import { register, RegisterInput } from "@api"
import { isEmail } from "@helper"
import { useFormCore } from "@hooks"
import useStore from "@store"
import { useRouter } from "next/router"
import { useMutation } from "react-query"

interface RegisterFormInput extends RegisterInput {
	confirmPassword: ""
}

const useRegister = () => {
	const initAuthData = useStore((s) => s.initAuthData)
	const router = useRouter()
	const { values, setValue, errors, setError, initError } = useFormCore<RegisterFormInput>({
		name: "",
		email: "",
		phone: "",
		date_of_birth: "",
		gender: "",
		avatar: null,
		store_name: "",
		branch_name: "",
		branch_address: "",
		username: "",
		password: "",
		confirmPassword: "",
	})

	const validate = () => {
		let isSubmittable = true

		// Check required fields
		let valuesKeys = Object.keys(values) as Array<keyof RegisterInput>
		valuesKeys
			.filter((key) => key !== "avatar")
			.forEach((key) => {
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
		onSuccess: (data) => {
			if (data.state === "fail") {
				initError({
					...errors,
					...Object.fromEntries(
						Object.keys(data.errors).map((errorKey) => [errorKey, data.errors[errorKey][0]])
					),
				})
			} else {
				initAuthData({ username: values.username, password: values.password })
				router.push("/login")
			}
		},
	})

	const mutateRegister = () => {
		if (validate()) {
			console.log("Input", values)
			mutate()
		}
	}

	return {
		isLoading,
		values,
		setValue,
		errors,
		validate,
		mutateRegister,
	}
}
export default useRegister
