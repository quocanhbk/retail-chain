import { deleteSupplier } from "@api"
import { Text } from "@chakra-ui/react"
import { SubmitConfirmAlert } from "@components/shared"
import { useRouter } from "next/router"
import { useMutation } from "react-query"

interface DeleteSupplierPopupProps {
	supplierId: number
	supplierName?: string
	isOpen: boolean
	onClose: () => void
}

const DeleteSupplierPopup = ({ supplierId, supplierName, isOpen, onClose }: DeleteSupplierPopupProps) => {
	const router = useRouter()
	const { mutate, isLoading } = useMutation(deleteSupplier, {
		onSuccess: () => {
			onClose()
			router.push("/admin/manage/supplier")
		},
	})

	const handleDelete = () => {
		if (!supplierId) return
		mutate(supplierId)
	}

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
			<Text>{`Bạn chắc chắn muốn xóa ${supplierName}`}</Text>
		</SubmitConfirmAlert>
	)
}

export default DeleteSupplierPopup
