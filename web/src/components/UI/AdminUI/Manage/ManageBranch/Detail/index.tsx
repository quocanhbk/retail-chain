import { createBranch, CreateBranchInput, editBranch, getBranch } from "@api"
import { Box, Button, chakra, Flex, HStack } from "@chakra-ui/react"
import { BackableTitle, TextControl } from "@components/shared"
import { useChakraToast, useFormCore } from "@hooks"
import router from "next/router"
import { FormEvent, useEffect, useRef, useState } from "react"
import { useMutation, useQuery, useQueryClient } from "react-query"
import { baseURL } from "src/api/fetcher"
import DeleteBranchPopup from "./DeleteBranchPopup"
import ImageInput from "../ImageInput"

interface BranchDetailUIProps {
	id: number
}

const BranchDetailUI = ({ id }: BranchDetailUIProps) => {
	const { refetch, data } = useQuery(["branch", id], () => getBranch(id), {
		enabled: false,
		onSuccess: data => {
			initForm({ ...data, image: `${baseURL}/branch/image/${data.image}` })
		}
	})

	const { values, setValue, errors, setError, initForm } = useFormCore<CreateBranchInput>({
		name: "",
		address: "",
		image: null
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

	const { mutate, isLoading } = useMutation(() => editBranch(id, values), {
		onSuccess: () => {
			toast({
				title: "Chỉnh sửa chi nhánh thành công",
				status: "success"
			})
			qc.invalidateQueries("branches")
			router.push("/admin/manage/branch")
		},
		onError: (err: any) => {
			console.log(err.response.data.message)

			toast({
				title: err.response.data.message,
				status: "error"
			})
		}
	})

	const handleCreateBranch = (e: FormEvent<HTMLFormElement>) => {
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

	const [readOnly, setReadOnly] = useState(true)

	useEffect(() => {
		if (readOnly) {
			refetch()
		}
	}, [readOnly])

	const [confirmDelete, setConfirmDelete] = useState(false)

	return (
		<Box p={4}>
			<BackableTitle
				text={readOnly ? "Xem chi nhánh" : "Chỉnh sửa chi nhánh"}
				backPath="/admin/manage/branch"
				mb={4}
			/>
			<Box w="24rem" maxW="full">
				<chakra.form onSubmit={handleCreateBranch}>
					<ImageInput
						file={values.image ?? "/images/store.jpg"}
						onSubmit={f => setValue("image", f)}
						readOnly={readOnly}
					/>
					<TextControl
						label="Tên chi nhánh"
						value={values.name}
						onChange={value => setValue("name", value)}
						error={errors.name}
						inputRef={inputRef}
						readOnly={readOnly}
					/>
					<TextControl
						label="Địa chỉ chi nhánh"
						value={values.address}
						onChange={value => setValue("address", value)}
						error={errors.address}
						readOnly={readOnly}
					/>
					<Flex w="full" align="center" justify="space-between">
						<HStack>
							<Button isLoading={isLoading} type="submit" w="6rem">
								{"Chỉnh sửa"}
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
			<DeleteBranchPopup
				branchId={id}
				branchName={data?.name}
				isOpen={confirmDelete}
				onClose={() => setConfirmDelete(false)}
			/>
		</Box>
	)
}

export default BranchDetailUI
