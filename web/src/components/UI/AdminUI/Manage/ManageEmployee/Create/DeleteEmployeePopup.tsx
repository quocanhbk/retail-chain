import { deleteEmployee } from "@api"
import { Text } from "@chakra-ui/react"
import { SubmitConfirmAlert } from "@components/shared"
import { useRouter } from "next/router"
import { useMutation } from "react-query"

interface DeleteBranchPopupProps {
	employeeId?: number
	employeeName?: string
	isOpen: boolean
	onClose: () => void
}

const DeleteEmployeePopup = ({ employeeId, employeeName, isOpen, onClose }: DeleteBranchPopupProps) => {
	const router = useRouter()
	const { mutate, isLoading } = useMutation(deleteEmployee, {
		onSuccess: () => {
			onClose()
			router.push("/admin/manage/employee")
		}
	})

	const handleDelete = () => {
		if (!employeeId) return
		mutate(employeeId)
	}

	return (
		<SubmitConfirmAlert
			title="Xóa nhân viên"
			isOpen={isOpen}
			onClose={onClose}
			onConfirm={handleDelete}
			confirmText="Xóa"
			cancelText="Hủy"
			color="red"
			isLoading={isLoading}
		>
			<Text>{`Bạn chắc chắn muốn xóa ${employeeName}`}</Text>
		</SubmitConfirmAlert>
	)
}

export default DeleteEmployeePopup
