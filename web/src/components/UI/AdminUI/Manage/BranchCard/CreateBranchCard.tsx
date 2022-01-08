import { createBranch, CreateBranchInput } from "@api"
import { Box, Flex, IconButton, Text } from "@chakra-ui/react"
import { Motion, TextControl, UploadInput } from "@components/shared"
import SubmitConfirmAlert from "@components/shared/SubmitConfirmAlert"
import { useChakraToast, useFormCore } from "@hooks"
import { useEffect, useRef, useState } from "react"
import { FaPlus } from "react-icons/fa"
import { useMutation, useQueryClient } from "react-query"
import Container from "./Container"

const CreateBranchCard = ({ index }: { index: number }) => {
	const [isOpen, setIsOpen] = useState(false)

	const { values, setValue, errors, setError, initForm } = useFormCore<CreateBranchInput>({
		name: "",
		address: "",
		image: null,
	})

	const toast = useChakraToast()

	const qc = useQueryClient()

	const inputRef = useRef(null)

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
			setIsOpen(false)
			qc.invalidateQueries("branches")
		},
		onError: (err: any) => {
			console.log(err.response.data.message)

			toast({
				title: err.response.data.message,
				status: "error",
			})
		},
	})

	const handleCreateBranch = () => {
		if (validate()) {
			mutate()
		}
	}

	useEffect(() => {
		initForm()
	}, [isOpen])

	return (
		<>
			<Container custom={index}>
				<Flex direction={"column"} align="center" justify="center" p={4} h="full">
					<Text fontSize={"lg"} fontWeight={"bold"} mb={2}>
						Tạo chi nhánh
					</Text>
					<Box p={4} rounded="full" background="blackAlpha.100" bg="telegram.500" color="white">
						<FaPlus />
					</Box>
				</Flex>
			</Container>
			<SubmitConfirmAlert
				title="Tạo chi nhánh"
				isOpen={isOpen}
				onClose={() => setIsOpen(false)}
				onConfirm={handleCreateBranch}
				isLoading={isLoading}
				leastDestructiveRef={inputRef}
				cancelText="Hủy"
				confirmText="Xác nhận"
			>
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
				<UploadInput
					label="Ảnh đại diện"
					file={values.image as File | null}
					onSubmit={f => setValue("image", f)}
				/>
			</SubmitConfirmAlert>
		</>
	)
}

export default CreateBranchCard
