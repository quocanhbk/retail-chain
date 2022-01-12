import { getBranches, createEmployee } from "@api"
import { useChakraToast, useFormCore } from "@hooks"
import { useRouter } from "next/router"
import { useMutation, useQuery } from "react-query"
import { CreateEmployeeInput } from "src/api/employee"

const useCreateEmployee = () => {
	const router = useRouter()
	const toast = useChakraToast()

	const { values, setValue, errors, setError } = useFormCore<CreateEmployeeInput>({
		branch_id: router.query.branch_id ? parseInt(router.query.branch_id as string) : 0,
		name: "",
		email: "",
		password: "",
		password_confirmation: "",
		avatar: null,
		birthday: null,
		phone: "",
		roles: [],
		gender: "",
	})

	const { data: branches } = useQuery("branches", () => getBranches(), { initialData: [] })

	const validate = () => {
		let isSubmittable = true
		;["name", "email", "password"].forEach(field => {
			if (!values[field]) {
				setError(field as keyof CreateEmployeeInput, "Bắt buộc")
				isSubmittable = false
			}
		})

		if (values.password !== values.password_confirmation) {
			setError("password_confirmation", "Mật khẩu không khớp")
			isSubmittable = false
		}

		if (values.roles.length === 0) {
			setError("roles", "Cần chọn ít nhất 1 quyền")
		}

		if (values.branch_id === 0) {
			setError("branch_id", "Bắt buộc")
			isSubmittable = false
		}

		if (values.birthday !== null && Object.values(values.birthday).some(v => v === null)) {
			setError("birthday", "Ngày sinh không hợp lệ")
			isSubmittable = false
		}

		return isSubmittable
	}

	const { mutate, isLoading } = useMutation(() => createEmployee(values), {
		onSuccess: () => {
			toast({ title: "Tạo nhân viên thành công", status: "success" })
			router.push("/admin/manage/employee")
		},
		onError: (err: any) => {
			console.log(err)
			const errors = err.response.data.errors
			if (errors) {
				Object.entries(errors).forEach(([key, value]) => {
					setError(key as keyof CreateEmployeeInput, (value as any)[0])
				})
			}
			toast({ title: "Tạo nhân viên thất bại", status: "error" })
		},
	})

	const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
		e.preventDefault()
		if (!validate()) {
			return
		}
		mutate()
	}

	return {
		values,
		setValue,
		errors,
		setError,
		branches,
		handleSubmit,
		isLoading,
	}
}

export default useCreateEmployee
