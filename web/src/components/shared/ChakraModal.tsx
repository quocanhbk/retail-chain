import { Modal, ModalOverlay, ModalContent, ModalHeader, ModalCloseButton, ModalBody, ModalProps } from "@chakra-ui/react"

interface ChakraModalProps extends ModalProps {
	isOpen: boolean
	onClose: () => void
	title: string
	children: React.ReactNode
}

export const ChakraModal = ({ isOpen, onClose, title, children, ...rest }: ChakraModalProps) => {
	return (
		<Modal isOpen={isOpen} onClose={onClose} {...rest}>
			<ModalOverlay />
			<ModalContent>
				<ModalHeader>{title}</ModalHeader>
				<ModalCloseButton />
				<ModalBody pb={4}>{children}</ModalBody>
			</ModalContent>
		</Modal>
	)
}

export default ChakraModal
