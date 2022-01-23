import { editSupplier, getSupplier ,CreateSupplierInput} from "@api"
import { Box, Button, chakra, Flex, HStack, Stack } from "@chakra-ui/react"
import { BackableTitle, TextControl } from "@components/shared"
import { useChakraToast, useFormCore } from "@hooks"
import router from "next/router"
import { FormEvent, useEffect, useRef, useState } from "react"
import { useMutation, useQuery, useQueryClient } from "react-query"
import DeleteSupplierPopup from "./DeleteSupplierPopup"
// import ImageInput from "../ImageInput"

interface SupplierDetailUiProps {
	id: number
}

const DetailSupplierUI = ({id} : SupplierDetailUiProps) => {
    const { refetch, data } = useQuery(["supplier", id], () => getSupplier(id), {
		enabled: false,
		onSuccess: data => {
			initForm({ ...data})
		},
        // onSuccess: data => {
		// 	initForm({ ...data, image: `${baseURL}/branch/image/${data.image}` })
		// },
	})
    
	const { values, setValue, errors, setError , initForm} = useFormCore<CreateSupplierInput>({
		name: "",
		address: "",
		email: "",
		phone: "",
		tax: "",
		note: ""
	})
    const [readOnly, setReadOnly] = useState(true)
	const toast = useChakraToast()

	const qc = useQueryClient()

	const inputRef = useRef<HTMLInputElement>(null)

	const validate = () => {
		let isSubmittable = true
		if (!values.name) {
			setError("name", "Tên nhà cung cấp không được để trống")
			isSubmittable = false
		}
		if (!values.address) {
			setError("address", "Địa chỉ không được để trống")
			isSubmittable = false
		}
		if (!values.email) {
			setError("email", "Email không được để trống")
			isSubmittable = false
		}
		return isSubmittable
	}

	const { mutate, isLoading } = useMutation(() => editSupplier(id,values), {
		onSuccess: () => {
			toast({
				title: "Chỉnh sửa chi nhánh thành công",
				status: "success",
			})
			qc.invalidateQueries("suppliers")
			router.push("/admin/manage/supplier")
		},
		onError: (err: any) => {
			console.log(err.response.data.message)

			toast({
				title: err.response.data.message,
				status: "error",
			})
		},
	})

	const handleCreateSupplier = (e: FormEvent<HTMLFormElement>) => {
		e.preventDefault()
        if (readOnly) {
			setReadOnly(false)
			return
		}
		if (validate()) {
			mutate()
		}
	}

	useEffect(() => {
		inputRef.current?.focus()
	}, [])

    useEffect(() => {
		if (readOnly) {
			refetch()
		}
	}, [readOnly])
    const [confirmDelete, setConfirmDelete] = useState(false)
	return (
		<Box p={4}>
			{/* <BackableTitle text="Tạo nhà cung cấp" backPath="/admin/manage/supplier" mb={4} /> */}
			<BackableTitle text={readOnly ? "Xem nhà cung cấp" : "Chỉnh sửa nhà cung cấp"} backPath="/admin/manage/supplier" mb={4} />
			<Box w="50rem" maxW="full">
				<chakra.form onSubmit={handleCreateSupplier}>
					{/* <ImageInput
						file={(values.image as File) ?? "/images/store.jpg"}
						onSubmit={f => setValue("image", f)}
					/> */}
					<Stack direction={["column", "row"]} justify="space-between" spacing={8}>
						<Box w="full" maxW="24rem">
							<TextControl
								label="Tên nhà cung cấp"
								value={values.name}
								onChange={value => setValue("name", value)}
								error={errors.name}
								inputRef={inputRef}
								readOnly={readOnly}
							/>
							<TextControl
								label="Địa chỉ nhà cung cấp"
								value={values.address}
								onChange={value => setValue("address", value)}
								error={errors.address}
								readOnly={readOnly}
							/>
							<TextControl
								label="Email nhà cung cấp"
								value={values.email}
								onChange={value => setValue("email", value)}
								error={errors.email}
								readOnly={readOnly}
							/>
						</Box>

						<Box w="full" maxW="24rem">
							<TextControl
								label="Số điện thoại nhà cung cấp"
								value={values.phone || ""}
								onChange={value => setValue("phone", value)}
								error={errors.phone}
								readOnly={readOnly}
							/>
							<TextControl
								label="Mã số thuế"
								value={values.tax}
								onChange={value => setValue("tax", value)}
								error={errors.tax}
								readOnly={readOnly}
							/>
							<TextControl
								label="Ghi chú"
								value={values.note}
								onChange={value => setValue("note", value)}
								error={errors.note}
								readOnly={readOnly}
							/>
						</Box>
					</Stack>
					<Flex w="24rem" align="center" justify="space-between">
						<HStack>
							<Button isLoading={isLoading} type="submit" w="6rem">
								{readOnly ? "Chỉnh sửa" : "Lưu thay đổi"}
							</Button>
							{!readOnly && (
								<Button variant="ghost" onClick={() => setReadOnly(true)} w="6rem">
									Hủy
								</Button>
							)}
						</HStack>
						<Button colorScheme={"red"} variant="ghost" onClick={() => setConfirmDelete(true)} w="6rem">
							Xóa
						</Button>
					</Flex>
				</chakra.form>
			</Box>
			<DeleteSupplierPopup supplierId={id} supplierName={data?.name} isOpen={confirmDelete} onClose={() => setConfirmDelete(false)} />
		</Box>
	)
}

export default DetailSupplierUI
