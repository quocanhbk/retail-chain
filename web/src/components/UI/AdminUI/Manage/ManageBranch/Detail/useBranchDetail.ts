import {
	CreateBranchInput,
	deleteEmployee,
	editBranch,
	getBranch,
	getEmployeesByBranchId,
	transferManyEmployees,
	updateBranchImage
} from "@api"
import { useChakraToast, useFormCore } from "@hooks"
import { useState } from "react"
import { useMutation, useQuery, useQueryClient } from "react-query"

const useBranchDetail = (id: number) => {
	const toast = useChakraToast()
	const qc = useQueryClient()

	const [isAddingEmployee, setIsAddingEmployee] = useState<"create" | "transfer" | null>(null)

	const { data: branch, isLoading: isLoadingBranch } = useQuery(["branch", id], () => getBranch(id), {
		onSuccess: data => initForm({ name: data.name, address: data.address })
	})
	const { data: employees, isLoading: isLoadingEmployees } = useQuery(["employees_by_branch", id], () => getEmployeesByBranchId(id))
	const isLoading = isLoadingBranch || isLoadingEmployees

	const { mutate: mutateDeleteEmployee, isLoading: isDeletingEmployee } = useMutation(deleteEmployee)
	const [confirmDelete, setConfirmDelete] = useState<number | null>(null)

	const { mutate: mutateTransferManyEmployees, isLoading: isTransferringManyEmployees } = useMutation(transferManyEmployees)
	const [confirmTransferManyEmployees, setConfirmTransferManyEmployees] = useState(false)

	const { values, setValue, initForm } = useFormCore<Pick<CreateBranchInput, "name" | "address">>({
		name: "",
		address: ""
	})

	const { mutate: mutateEditBranch, isLoading: isEditingBranch } = useMutation(() => editBranch(id, values), {
		onSuccess: () => {
			toast({
				title: "Cập nhật thành công",
				status: "success"
			})
			qc.invalidateQueries(["branch", id])
		}
	})

	const { mutate: mutateUpdateImage } = useMutation<unknown, unknown, File>(image => updateBranchImage(id, image), {
		onSuccess: () => {
			toast({
				title: "Cập nhật thành công",
				status: "success"
			})
			qc.invalidateQueries(["branch", id])
		}
	})

	const handleUpdateBranch = () => {
		if (!values.name) {
			toast({
				title: "Tên chi nhánh không được để trống",
				status: "error"
			})
			return
		}
		if (!values.address) {
			toast({
				title: "Địa chỉ không được để trống",
				status: "error"
			})
			return
		}
		mutateEditBranch()
	}

	const handleUpdateImage = (f: File | null) => {
		if (!f) return
		mutateUpdateImage(f)
	}

	return {
		branch,
		employees,
		isLoading,
		isAddingEmployee,
		setIsAddingEmployee,
		mutateDeleteEmployee,
		isDeletingEmployee,
		confirmDelete,
		setConfirmDelete,
		mutateTransferManyEmployees,
		isTransferringManyEmployees,
		confirmTransferManyEmployees,
		setConfirmTransferManyEmployees,
		handleUpdateBranch,
		isEditingBranch,
		values,
		setValue,
		handleUpdateImage
	}
}

export default useBranchDetail
