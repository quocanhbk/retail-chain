import { createBranch, CreateBranchInput } from "@api"
import { Box, Button, chakra } from "@chakra-ui/react"
import { BackableTitle, TextControl } from "@components/shared"
import { useChakraToast, useFormCore } from "@hooks"
import router from "next/router"
import { FormEvent, useEffect, useRef } from "react"
import { useMutation, useQueryClient } from "react-query"
import ImageInput from "../ImageInput"
import useCreateBranch from "./useCreateBranch"
interface CreateBranchUIProps {
	id?: number
}

const CreateBranchUI = ({ id }: CreateBranchUIProps) => {
	const { values, setValue, errors,  handleCreateBranch, isLoading, inputRef } = useCreateBranch(id)

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
