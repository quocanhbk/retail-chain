// import dataFetcher from "@api"
import { TextControl } from "@components/shared"
import SubmitConfirmAlert from "@components/shared/SubmitConfirmAlert"
import { useState } from "react"
import { useMutation, useQueryClient } from "react-query"

interface AddCategoryModalProps {
	isOpen: boolean
	onClose: () => void
}

const AddCategoryModal = ({ isOpen, onClose }: AddCategoryModalProps) => {
	const [text, setText] = useState("")
	const queryClient = useQueryClient()
	// const { mutate, isLoading } = useMutation(() => dataFetcher.addCategory(text), {
	// 	onSuccess: () => {
	// 		queryClient.invalidateQueries("category")
	// 		onClose()
	// 	},
	// })
	return (
		<></>
		// <SubmitConfirmAlert
		// 	isOpen={isOpen}
		// 	onClose={onClose}
		// 	title="Thêm danh mục"
		// 	confirmText="Thêm"
		// 	cancelText="Hủy"
		// 	onConfirm={mutate}
		// 	isLoading={isLoading}
		// >
		// 	<TextControl label="" value={text} onChange={setText} />
		// </SubmitConfirmAlert>
	)
}

export default AddCategoryModal
