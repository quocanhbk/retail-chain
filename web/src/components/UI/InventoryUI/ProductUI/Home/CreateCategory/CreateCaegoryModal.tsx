import { Category } from "@api"
import { Button, chakra, Flex, Input } from "@chakra-ui/react"
import { ChakraModal, FormControl } from "@components/shared"
import { useRef } from "react"
import useCreateCategory from "./useCreateCategory"

interface CreateSupplierModalProps {
	isOpen: boolean
	onClose: () => void
	onSelectCategory: (category: Category | null) => void
}

const CreateCategoryModal = ({ isOpen, onClose , onSelectCategory}:  CreateSupplierModalProps) => {
	const { values, setValue,handleSubmit, isLoading } = useCreateCategory(isOpen, onClose, onSelectCategory)
	const ref = useRef<HTMLInputElement>(null)
	return (
		<ChakraModal title="Tạo danh mục" isOpen={isOpen} onClose={onClose} initialFocusRef={ref}>
			<chakra.form onSubmit={handleSubmit}>
				<FormControl label="Tên danh mục" isRequired={true}>
					<Input
						value={values.name}
						onChange={(e) => setValue("name",e.target.value)}
						placeholder=" Tên danh mục"
					/>
				</FormControl>

				<Flex justify="flex-end" mt={6}>
					<Button onClick={onClose} variant="ghost">
						Hủy
					</Button>
					<Button type="submit" ml={3} isLoading={isLoading} >
						{"Xác nhận"}
					</Button>
				</Flex>
			</chakra.form>
		</ChakraModal>
	)
}

export default CreateCategoryModal
