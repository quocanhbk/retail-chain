import dataFetcher from "@api"
import { Text } from "@chakra-ui/layout"
import SubmitConfirmAlert from "@components/shared/SubmitConfirmAlert"
import { useMutation, useQueryClient } from "react-query"

interface DeleteCategoryModalProps {
	deletingId: number | null
	setDeletingId: (id: number | null) => void
	categoryName?: string
}

const DeleteCategoryModal = ({ deletingId, setDeletingId, categoryName }: DeleteCategoryModalProps) => {
	const queryClient = useQueryClient()
	const { mutate: mutateDeleteCategory, isLoading: isDeleting } = useMutation(
		() => dataFetcher.deleteCategory(deletingId!),
		{
			onSuccess: () => {
				queryClient.invalidateQueries("category")
				setDeletingId(null)
			},
		}
	)
	return (
		<SubmitConfirmAlert
			isOpen={!!deletingId}
			onClose={() => setDeletingId(null)}
			title={`Xóa danh mục`}
			cancelText="Hủy"
			confirmText="Xóa"
			color="red"
			onConfirm={mutateDeleteCategory}
			isLoading={isDeleting}
		>
			<Text>{`Bạn có muốn xóa ${categoryName}`}</Text>
		</SubmitConfirmAlert>
	)
}

export default DeleteCategoryModal
