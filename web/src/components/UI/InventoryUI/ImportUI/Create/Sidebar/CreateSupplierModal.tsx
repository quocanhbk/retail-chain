import { Supplier } from "@api"
import { Button, chakra, Flex, Input } from "@chakra-ui/react"
import { ChakraModal, FormControl } from "@components/shared"
import { useRef } from "react"
import useCreateSupplier from "./useCreateSupplier"

interface CreateSupplierModalProps {
	isOpen: boolean
	onClose: () => void
	onSelectSupplier: (supplier: Supplier | null) => void
}

const CreateSupplierModal = ({ isOpen, onClose, onSelectSupplier }: CreateSupplierModalProps) => {
	const { formControlData, handleSubmit, isLoading } = useCreateSupplier(isOpen, onClose, onSelectSupplier)
	const ref = useRef<HTMLInputElement>(null)
	return (
		<ChakraModal title="Tạo nhà cung cấp" isOpen={isOpen} onClose={onClose} initialFocusRef={ref}>
			<chakra.form onSubmit={handleSubmit}>
				{formControlData.map((formControl, idx) => (
					<FormControl key={formControl.label} label={formControl.label} isRequired={formControl.isRequired}>
						<Input
							value={formControl.value}
							onChange={formControl.onChange}
							placeholder={formControl.placeholder}
							ref={idx === 1 ? ref : undefined}
						/>
					</FormControl>
				))}
				<Flex justify="flex-end" mt={6}>
					<Button onClick={onClose} variant="ghost">
						Hủy
					</Button>
					<Button type="submit" ml={3} isLoading={isLoading}>
						{"Xác nhận"}
					</Button>
				</Flex>
			</chakra.form>
		</ChakraModal>
	)
}

export default CreateSupplierModal
