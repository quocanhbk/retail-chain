import { deleteReturnPurchaseSheet } from "@api"
import { useChakraToast } from "@hooks"
import { id } from "date-fns/locale"
import router from "next/router"
import { useState } from "react"
import { useMutation } from "react-query"

const useDeleteReturnPurchaseSheet = (purchaseSheetId?: number) => {
	const toast = useChakraToast()

	const { mutate: mutateDelete, isLoading: isDeleting } = useMutation(() => deleteReturnPurchaseSheet(purchaseSheetId!), {
		onSuccess: () => {
			toast({
				title: "Xóa phiếu trả hàng nhập thành công",
				status: "success"
			})
			router.push("/main/inventory/return-import")
		},
		onError: () => {
			toast({
				title: "Xóa phiếu trả hàng nhập thất bại",
				message: "Vui lòng thử lại sau",
				status: "error"
			})
		}
	})

	const [confirmDelete, setConfirmDelete] = useState(false)

	return {
		confirmDelete,
		setConfirmDelete,
		mutateDelete,
		isDeleting
	}
}

export default useDeleteReturnPurchaseSheet
