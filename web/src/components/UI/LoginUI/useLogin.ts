import dataFetcher, { LoginInput, login } from "@api"
import { useFormCore } from "@hooks"
import useStore from "@store"
import { useRouter } from "next/router"
import { useEffect } from "react"
import { useMutation } from "react-query"

const useRegister = () => {
	const authData = useStore(s => s.authData)
	const { info, initInfo } = useStore(s => ({ info: s.info, initInfo: s.initInfo }))
	const router = useRouter()
	const { values, setValue, errors, setError } = useFormCore<LoginInput>(authData)
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

	useEffect(() => {
		if (info?.token) router.push("/sale")
	}, [router, info?.token])

	const { mutate, isLoading } = useMutation(() => login(values), {
		onSuccess: data => {
			if (data.state === "fail") {
				setError("username", data.errors)
			} else {
				initInfo(data)
				dataFetcher.init(data.user_info.store_id, data.user_info.branch_id, data.token)
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
