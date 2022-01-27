import { createBranch, CreateBranchInput } from "@api"
import { createSupplier, CreateSupplierInput } from "@api"
import { Box, Button, chakra, Input, Stack } from "@chakra-ui/react"
import { FormControl } from "@components/shared"
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
		code:"",
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
							<FormControl label={"Mã nhà cung cấp"} mb={4} isRequired={false}>
								<Input
									value={values.code}
									onChange={e => setValue("code", e.target.value)}
									placeholder="Mã tự động"
									type="text"
								/>
							</FormControl>
							<FormControl label="Tên nhà cung cấp" mb={4} isRequired={true}>
								<Input
									value={values.name}
									onChange={e => setValue("name", e.target.value)}
									error={errors.name}
									type="text"
								/>
							</FormControl>
							<FormControl label="Địa chỉ nhà cung cấp" mb={4} isRequired={false}>
								<Input
									value={values.address}
									onChange={e => setValue("address", e.target.value)}
									error={errors.address}
									type="text"
								/>
							</FormControl>
							<FormControl label="Email nhà cung cấp" mb={4} isRequired={false}>
								<Input
									value={values.email}
									onChange={e => setValue("email", e.target.value)}
									error={errors.email}
									type="email"
								/>
							</FormControl>
						</Box>
						<Box w="full" maxW="24rem" mr={4}>
							<FormControl label="Số điện thoại nhà cung cấp" mb={4} isRequired={true}>
								<Input
									value={values.phone || ""}
									onChange={e => setValue("phone", e.target.value)}
									error={errors.phone}
									type="phone"
								/>
							</FormControl>
							<FormControl label="Mã số thuế" mb={4} isRequired={false}>
								<Input
									value={values.tax}
									onChange={e => setValue("tax", e.target.value)}
									error={errors.tax}
									type="text"
								/>
							</FormControl>
							<FormControl label="Ghi chú" mb={4} isRequired={false}>
								<Input
									value={values.note}
									onChange={e => setValue("note", e.target.value)}
									type="text"
								/>
							</FormControl>
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
