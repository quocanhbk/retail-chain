import { createBranch, CreateBranchInput } from "@api"
import { createSupplier, CreateSupplierInput } from "@api"
import { Box, Button, chakra, Stack } from "@chakra-ui/react"
import { BackableTitle, TextControl } from "@components/shared"
import { useChakraToast, useFormCore } from "@hooks"
import router from "next/router"
import { FormEvent, useEffect, useRef } from "react"
import { useMutation, useQueryClient } from "react-query"
// import ImageInput from "../ImageInput"

const CreateSupplierUI = () => {
	const { values, setValue, errors, setError } = useFormCore<CreateSupplierInput>({
		name: "",
		address: "",
		email: "",
		phone: "",
		tax: "",
		note: ""
	})
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

	const { mutate, isLoading } = useMutation(() => createSupplier(values), {
		onSuccess: () => {
			toast({
				title: "Tạo chi nhánh thành công",
				status: "success"
			})
			qc.invalidateQueries("suppliers")
			router.push("/admin/manage/supplier")
		},
		onError: (err: any) => {
			console.log(err.response.data.message)

			toast({
				title: err.response.data.message,
				status: "error"
			})
		}
	})

	const handleCreateSupplier = (e: FormEvent<HTMLFormElement>) => {
		e.preventDefault()
		if (validate()) {
			mutate()
		}
	}

	useEffect(() => {
		inputRef.current?.focus()
	}, [])

	return (
		<Box p={4}>
			<BackableTitle text="Tạo nhà cung cấp" backPath="/admin/manage/supplier" mb={4} />
			<Box maxW="50rem">
				<chakra.form onSubmit={handleCreateSupplier}>
					<Stack direction={["column", "row"]} justify="space-between" spacing={8}>
						<Box w="full" maxW="24rem">
							{/* <ImageInput
						file={(values.image as File) ?? "/images/store.jpg"}
						onSubmit={f => setValue("image", f)}
					/> */}
							<TextControl
								label="Tên nhà cung cấp"
								value={values.name}
								onChange={value => setValue("name", value)}
								error={errors.name}
								inputRef={inputRef}
							/>
							<TextControl
								label="Địa chỉ nhà cung cấp"
								value={values.address}
								onChange={value => setValue("address", value)}
								error={errors.address}
							/>
							<TextControl
								label="Email nhà cung cấp"
								value={values.email}
								onChange={value => setValue("email", value)}
								error={errors.email}
							/>
							
						</Box>
						<Box w="full" maxW="24rem" mr={4}>
						<TextControl
								label="Số điện thoại nhà cung cấp"
								value={values.phone || ""}
								onChange={value => setValue("phone", value)}
								error={errors.phone}
							/>
							<TextControl
								label="Mã số thuế"
								value={values.tax}
								onChange={value => setValue("tax", value)}
								error={errors.tax}
							/>
							<TextControl
								label="Ghi chú"
								value={values.note}
								onChange={value => setValue("note", value)}
								error={errors.note}
							/>
						</Box>
					</Stack>
					<Button isLoading={isLoading} type="submit">
						{"Tạo nhà cung cấp"}
					</Button>
				</chakra.form>
			</Box>
		</Box>
	)
}

export default CreateSupplierUI
