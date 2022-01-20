import { getBranches, createEmployee, getEmployee, updateEmployee, getEmployeeAvatar } from "@api"
import { dateToDateInput } from "@helper"
import { useChakraToast, useFormCore } from "@hooks"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import { useMutation, useQuery } from "react-query"
import { CreateEmployeeInput } from "src/api/employee"

const useCreateEmployee = (id: number | undefined) => {
	const router = useRouter()
	const toast = useChakraToast()
	const [readOnly, setReadOnly] = useState(!!id)
	const [confirmDelete, setConfirmDelete] = useState(false)
	const { values, setValue, initForm } = useFormCore<CreateEmployeeInput>({
		branch_id: router.query.branch_id ? parseInt(router.query.branch_id as string) : 0,
		name: "",
		email: "",
		password: "",
		password_confirmation: "",
		avatar: null,
		birthday: null,
		phone: "",
		roles: [],
		gender: ""
	})

	const { data: employee, refetch } = useQuery(["employee", id], () => getEmployee(id!), {
		enabled: false,
		onSuccess: data => {
			initForm({
				name: data.name,
				email: data.email,
				branch_id: data.employment.branch_id,
				avatar: getEmployeeAvatar(data.avatar_key),
				birthday: dateToDateInput(data.birthday),
				phone: data.phone,
				roles: data.employment.roles.map(r => r.role),
				gender: data.gender,
				password: "",
				password_confirmation: ""
			})
		}
	})

	useEffect(() => {
		if (readOnly) {
			refetch()
		}
	}, [readOnly])

	const { data: branches } = useQuery("branches", () => getBranches(), { initialData: [] })

	const validate = () => {
		if (values.password !== values.password_confirmation) {
			toast({
				title: "Mật khẩu không khớp",
				status: "error"
			})
			return false
		}

		if (values.roles.length === 0) {
			toast({
				title: "Vui lòng chọn ít nhất 1 quyền",
				status: "error"
			})
			return false
		}

		if (values.branch_id === 0) {
			toast({
				title: "Vui lòng chọn chi nhánh",
				status: "error"
			})
			return false
		}

		if (values.birthday !== null && Object.values(values.birthday).some(v => v === null)) {
			toast({
				title: "Ngày sinh không hợp lệ",
				status: "error"
			})
			return false
		}
		return true
	}

	const mutationFn = id ? () => updateEmployee(id, values) : () => createEmployee(values)
	const { mutate, isLoading } = useMutation(mutationFn, {
		onSuccess: () => {
			toast({ title: id ? "Chỉnh sửa nhân viên thành công" : "Tạo nhân viên thành công", status: "success" })
			router.push("/admin/manage/employee")
		},
		onError: (err: any) => {
			console.log(err)
			const errors = err.response.data.errors
			if (errors) {
				toast({ title: "Lỗi", status: "error", message: errors.join("\n") })
			}
		}
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
		branches,
		handleSubmit,
		isLoading,
		readOnly,
		setReadOnly,
		confirmDelete,
		setConfirmDelete,
		employee
	}
}

export default useCreateEmployee
