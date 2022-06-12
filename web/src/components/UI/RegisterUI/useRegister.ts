import { client, RegisterStoreInput, Store } from "@api"
import { useStoreActions } from "@store"
import { useRouter } from "next/router"
import { useMutation } from "react-query"
import { useForm } from "react-hook-form"
import * as Yup from "yup"
import { yupResolver } from "@hookform/resolvers/yup"
import { useEffect, useState } from "react"

const validationSchema = Yup.object().shape({
	name: Yup.string().required("Name is required"),
	email: Yup.string().email("Email is invalid").required("Email is required"),
	password: Yup.string().required("Password is required"),
	password_confirmation: Yup.string().required("Password confirmation is required"),
	remember: Yup.boolean()
})

const useRegister = () => {
	const router = useRouter()

	const setInfo = useStoreActions(s => s.setStoreInfo)

	const [generalError, setGeneralError] = useState("")

	const {
		register,
		handleSubmit,
		formState: { errors },
		watch
	} = useForm<RegisterStoreInput>({
		resolver: yupResolver(validationSchema)
	})

	useEffect(() => {
		const sub = watch(() => setGeneralError(""))
		return () => sub.unsubscribe()
	}, [watch])

	const { mutate: mutateRegisterStore, isLoading: isRegistering } = useMutation<Store, Error, RegisterStoreInput>(
		input => client.store.registerStore(input).then(res => res.data),
		{
			onSuccess: data => {
				setInfo(data)
				router.push("/admin")
			},
			onError: error => {
				setGeneralError(error.message)
			}
		}
	)

	const handleRegister = handleSubmit(input => mutateRegisterStore(input))

	return {
		handleRegister,
		errors,
		generalError,
		register,
		isRegistering
	}
}
export default useRegister
