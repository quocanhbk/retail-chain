import { Box, Button, chakra, Input, Stack } from "@chakra-ui/react"
import { FormControl } from "@components/shared"
import { BackableTitle } from "@components/shared"
import { IoMdCheckmarkCircle, IoMdSettings, IoMdTrash } from "react-icons/io"
import DeleteSupplierPopup from "./DeleteSupplierPopup"
import useCreateSupplier from "./useCreateSupplier"

interface CreateSupplierUIProps {
	id?: number
}

const CreateSupplierUI = ({ id }: CreateSupplierUIProps) => {
	const { formControlData, handleSubmit, isLoading, readOnly, data, confirmDelete, setConfirmDelete, currentId } = useCreateSupplier(id)

	const title = id ? (readOnly ? "Xem nhà cung cấp" : "Chỉnh sửa nhà cung cấp") : "Tạo nhà cung cấp"

	return (
		<Box p={4}>
			<BackableTitle text={title} backPath="/admin/manage/supplier" mb={4} />
			<Box w="full" maxW="24rem">
				<chakra.form onSubmit={handleSubmit}>
					{formControlData.map(formControl => (
						<FormControl
							key={formControl.label}
							label={formControl.label}
							isRequired={formControl.isRequired}
							isReadOnly={formControl.isReadOnly}
						>
							<Input value={formControl.value} onChange={formControl.onChange} placeholder={formControl.placeholder} />
						</FormControl>
					))}
					<Stack mt={4} pt={4} borderTop="1px" borderColor={"border.primary"} direction={"row"} spacing={4}>
						<Button
							isLoading={isLoading}
							type="submit"
							w="8rem"
							colorScheme={readOnly ? "yellow" : "telegram"}
							leftIcon={readOnly ? <IoMdSettings /> : <IoMdCheckmarkCircle />}
						>
							{readOnly ? "Chỉnh sửa" : "Xác nhận"}
						</Button>
						{!!currentId && (
							<Button
								w="8rem"
								variant="ghost"
								colorScheme={"red"}
								onClick={() => setConfirmDelete(true)}
								leftIcon={<IoMdTrash />}
							>
								{"Xoá"}
							</Button>
						)}
					</Stack>
				</chakra.form>
				<DeleteSupplierPopup isOpen={confirmDelete} onClose={() => setConfirmDelete(false)} supplierId={id} supplierName={data?.name} />
			</Box>
		</Box>
	)
}

export default CreateSupplierUI
