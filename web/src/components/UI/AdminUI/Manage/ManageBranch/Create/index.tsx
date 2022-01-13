import { createBranch, CreateBranchInput } from "@api"
import { Box, Button, chakra } from "@chakra-ui/react"
import { BackableTitle, TextControl } from "@components/shared"
import { useChakraToast, useFormCore } from "@hooks"
import router from "next/router"
import { FormEvent, useEffect, useRef } from "react"
import { useMutation, useQueryClient } from "react-query"
import ImageInput from "../ImageInput"

const CreateBranchUI = () => {
	const { values, setValue, errors, setError } = useFormCore<CreateBranchInput>({
		name: "",
		address: "",
		image: null,
	})
	const toast = useChakraToast()

	const qc = useQueryClient()

	const inputRef = useRef<HTMLInputElement>(null)

	const validate = () => {
		let isSubmittable = true
		if (!values.name) {
			setError("name", "Tên chi nhánh không được để trống")
			isSubmittable = false
		}
		if (!values.address) {
			setError("address", "Địa chỉ không được để trống")
			isSubmittable = false
		}
		return isSubmittable
	}

	const { mutate, isLoading } = useMutation(() => createBranch(values), {
		onSuccess: () => {
			toast({
				title: "Tạo chi nhánh thành công",
				status: "success",
			})
			qc.invalidateQueries("branches")
			router.push("/admin/manage/branch")
		},
		onError: (err: any) => {
			console.log(err.response.data.message)

			toast({
				title: err.response.data.message,
				status: "error",
			})
		},
	})

	const handleCreateBranch = (e: FormEvent<HTMLFormElement>) => {
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
			<BackableTitle text="Tạo chi nhánh" backPath="/admin/manage/branch" mb={4} />
			<Box w="24rem" maxW="full">
				<chakra.form onSubmit={handleCreateBranch}>
					<ImageInput
						file={(values.image as File) ?? "/images/store.jpg"}
						onSubmit={f => setValue("image", f)}
					/>
					<TextControl
						label="Tên chi nhánh"
						value={values.name}
						onChange={value => setValue("name", value)}
						error={errors.name}
						inputRef={inputRef}
					/>
					<TextControl
						label="Địa chỉ chi nhánh"
						value={values.address}
						onChange={value => setValue("address", value)}
						error={errors.address}
					/>
					<Button isLoading={isLoading} type="submit">
						{"Tạo chi nhánh"}
					</Button>
				</chakra.form>
			</Box>
		</Box>
	)
}

export default CreateBranchUI
