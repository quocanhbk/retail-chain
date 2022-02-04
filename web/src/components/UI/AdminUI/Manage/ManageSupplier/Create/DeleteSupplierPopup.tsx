import { deleteSupplier } from "@api"
import { Text } from "@chakra-ui/react"
import { SubmitConfirmAlert } from "@components/shared"
import { useChakraToast } from "@hooks"
import { useRouter } from "next/router"
import { useMutation } from "react-query"

interface DeleteSupplierPopupProps {
	supplierId?: number
	supplierName?: string
	isOpen: boolean
	onClose: () => void
}

const DeleteSupplierPopup = ({ supplierId, supplierName, isOpen, onClose }: DeleteSupplierPopupProps) => {
	const router = useRouter()
	const toast = useChakraToast()

	const { mutate, isLoading } = useMutation(deleteSupplier, {
		onSuccess: () => {
			router.push("/admin/manage/supplier")
			toast({
				title: "Xoá nhà cung cấp thành công",
				status: "success"
			})
		},
		onError: (e: any) => {
			toast({
				title: e.response.data.message || "Có lỗi xảy ra",
				message: e.response.data.error || "Vui lòng thử lại",
				status: "error"
			})
		}
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
