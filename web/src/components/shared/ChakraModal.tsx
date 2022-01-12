import { Modal, ModalOverlay, ModalContent, ModalHeader, ModalCloseButton, ModalBody } from "@chakra-ui/react"

interface ChakraModalProps {
	isOpen: boolean
	onClose: () => void
	title: string
	children: React.ReactNode
}

export const ChakraModal = ({ isOpen, onClose, title, children }: ChakraModalProps) => {
	return (
		<Modal isOpen={isOpen} onClose={onClose}>
			<ModalOverlay />
			<ModalContent>
				<ModalHeader>{title}</ModalHeader>
				<ModalCloseButton />
				<ModalBody>{children}</ModalBody>
			</ModalContent>
		</Modal>
	)
}

export default ChakraModal
