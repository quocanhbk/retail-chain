import { createBranch, CreateBranchInput, CreateEmployeeInput, getEmployees } from "@api"
import { useChakraToast, useFormCore } from "@hooks"
import { useRouter } from "next/router"
import { FormEvent, useState } from "react"
import { useMutation, useQuery, useQueryClient } from "react-query"

const useCreateBranch = () => {
	const toast = useChakraToast()
	const qc = useQueryClient()
	const router = useRouter()

	const [isAddingEmployee, setIsAddingEmployee] = useState<"create" | "transfer" | null>(null)
	const [confirmCreate, setConfirmCreate] = useState(false)

	const { values, setValue } = useFormCore<CreateBranchInput>({
		name: "",
		address: "",
		image: null,
		adding_employees: []
	})

	const { data: employeesData } = useQuery("employees", getEmployees, { initialData: [] })

	const addEmployee = (employee: CreateEmployeeInput) => {
		if (values.adding_employees.find(e => e.email === employee.email)) {
			toast({
				title: "Email bị đã được sử dụng",
				status: "error"
			})
			return
		}

		setValue("adding_employees", [...values.adding_employees, { ...employee, type: "create" }])
		setIsAddingEmployee(null)
	}

	const transferEmployees = (data: { id: number; roles: string[] }[]) => {
		const newEmployees = employeesData!
			.filter(e => data.find(d => d.id === e.id))
			.map(employee => ({
				name: employee.name,
				email: employee.email,
				phone: employee.phone,
				branch_id: employee.employment.branch_id,
				roles: data.find(d => d.id === employee.id)?.roles ?? [],
				type: "transfer" as const,
				id: employee.id
			}))
		setValue("adding_employees", [...values.adding_employees, ...newEmployees])
	}

	const removeEmployee = (email: string) => {
		setValue(
			"adding_employees",
			values.adding_employees.filter(e => e.email !== email)
		)
	}

	const { mutate: mutateCreateBranch, isLoading: isCreatingBranch } = useMutation(() => createBranch(values), {
		onSuccess: () => {
			toast({
				title: "Tạo chi nhánh thành công",
				status: "success"
			})
			qc.invalidateQueries("branches")
			setConfirmCreate(false)
			router.push("/admin/manage/branch")
		},
		onError: (err: any) => {
			setConfirmCreate(false)
			toast({
				title: err.response.data.message,
				status: "error"
			})
		}
	})

	const validate = () => {
		if (!values.name) {
			toast({
				title: "Tên chi nhánh không được để trống",
				status: "error"
			})
			return false
		}

		if (!values.address) {
			toast({
				title: "Địa chỉ không được để trống",
				status: "error"
			})
			return false
		}
		return true
	}

	const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
		e.preventDefault()
		if (validate()) setConfirmCreate(true)
	}

	return {
		values,
		setValue,
		handleSubmit,
		addEmployee,
		removeEmployee,
		isAddingEmployee,
		setIsAddingEmployee,
		transferEmployees,
		confirmCreate,
		setConfirmCreate,
		createBranch: mutateCreateBranch,
		isCreatingBranch
	}
}

export default useCreateBranch
