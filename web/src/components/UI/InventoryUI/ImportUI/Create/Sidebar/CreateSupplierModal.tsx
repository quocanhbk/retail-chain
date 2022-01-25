import { ChakraModal } from "@components/shared"

interface CreateSupplierModalProps {
	isOpen: boolean
	onClose: () => void
}

const CreateSupplierModal = ({ isOpen, onClose }: CreateSupplierModalProps) => {
	return (
		<ChakraModal title="Tạo nhà cung cấp" isOpen={isOpen} onClose={onClose}>
			Hello
		</ChakraModal>
	)
}

export default CreateSupplierModal
