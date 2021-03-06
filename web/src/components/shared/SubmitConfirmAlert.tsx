// * DESCRIPTION: this is a modal that confirm when you submit request, form, or procedure

import {
	AlertDialog,
	AlertDialogOverlay,
	AlertDialogContent,
	AlertDialogHeader,
	AlertDialogBody,
	AlertDialogFooter,
	Button,
	AlertDialogCloseButton,
	chakra
} from "@chakra-ui/react"
import { RefObject, useRef } from "react"

interface SubmitConfirmAlertProps {
	isOpen: boolean
	onClose: () => void
	onConfirm: () => void
	title: string
	cancelText?: string
	confirmText?: string
	color?: string
	leastDestructiveRef?: RefObject<any>
	isLoading?: boolean
	children: React.ReactNode
}

export const SubmitConfirmAlert = ({
	isOpen,
	onClose,
	onConfirm,
	title,
	children,
	cancelText = "Hủy",
	confirmText = "Xác nhận",
	color,
	leastDestructiveRef,
	isLoading
}: SubmitConfirmAlertProps) => {
	const cancelRef = useRef<HTMLButtonElement>(null)
	return (
		<AlertDialog isOpen={isOpen} onClose={onClose} leastDestructiveRef={leastDestructiveRef || cancelRef}>
			<AlertDialogOverlay>
				<AlertDialogContent>
					<chakra.form>
						<AlertDialogHeader fontSize="lg" fontWeight="semibold">
							{title}
						</AlertDialogHeader>
						<AlertDialogCloseButton onClick={onClose} />
						<AlertDialogBody>{children}</AlertDialogBody>

						<AlertDialogFooter>
							<Button ref={cancelRef} variant="ghost" colorScheme={color} onClick={onClose}>
								{cancelText}
							</Button>
							<Button type="submit" colorScheme={color} onClick={onConfirm} ml={3} isLoading={isLoading}>
								{confirmText}
							</Button>
						</AlertDialogFooter>
					</chakra.form>
				</AlertDialogContent>
			</AlertDialogOverlay>
		</AlertDialog>
	)
}

export default SubmitConfirmAlert
