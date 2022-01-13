import { deleteBranch } from "@api"
import { Text } from "@chakra-ui/react"
import { SubmitConfirmAlert } from "@components/shared"
import { useRouter } from "next/router"
import { useMutation } from "react-query"

interface DeleteBranchPopupProps {
	branchId: number
	branchName?: string
	isOpen: boolean
	onClose: () => void
}

const DeleteBranchPopup = ({ branchId, branchName, isOpen, onClose }: DeleteBranchPopupProps) => {
	const router = useRouter()
	const { mutate, isLoading } = useMutation(deleteBranch, {
		onSuccess: () => {
			onClose()
			router.push("/admin/manage/branch")
		},
	})

	const handleDelete = () => {
		if (!branchId) return
		mutate(branchId)
	}

	console.log(branchId)

	return (
		<SubmitConfirmAlert
			title="Xóa chi nhánh"
			isOpen={isOpen}
			onClose={onClose}
			onConfirm={handleDelete}
			confirmText="Xóa"
			cancelText="Hủy"
			color="red"
			isLoading={isLoading}
		>
			<Text>{`Bạn chắc chắn muốn xóa ${branchName}`}</Text>
		</SubmitConfirmAlert>
	)
}

export default DeleteBranchPopup
