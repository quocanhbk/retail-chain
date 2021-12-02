import { logout } from "@api"
import { useRouter } from "next/router"
import { useMutation } from "react-query"

export const useLogout = () => {
	const router = useRouter()

	const mutation = useMutation(() => logout(), {
		onSuccess: () => {
			router.push("/")
		},
	})

	return mutation
}
