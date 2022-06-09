import { Category, CategoryInput, createCategory } from "@api"
import { useChakraToast, useFormCore } from "@hooks"
import { FormEvent, useEffect } from "react"
import { useMutation, useQueryClient } from "react-query"

const useCreateCategory = (isOpen: boolean, onClose: () => void, onSelectCategory: (category: Category | null) => void) => {
	const toast = useChakraToast()
	const qc = useQueryClient()

	const { values, setValue, initForm } = useFormCore<CategoryInput>({
		name: ""
	})

	const validate = () => {
		if (!values.name) {
			toast({
				title: "Tên danh mục là bắt buộc",
				message: "Vui lòng nhập tên danh mục",
				status: "error"
			})
			return false
		}
		return true
	}

	const { mutate: mutateCreateCategory, isLoading } = useMutation(() => createCategory(values), {
		onSuccess: data => {
			qc.invalidateQueries("categories")
			onSelectCategory(data)
			onClose()
		},
		onError: (e: any) => {
			console.log(e.response)
			toast({
				title: e.response.data.message || "Có lỗi xảy ra",
				message: e.response.data.error || "Vui lòng thử lại",
				status: "error"
			})
		}
	})

	const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
		e.preventDefault()
		if (validate()) {
			mutateCreateCategory()
		}
	}

	useEffect(() => {
		initForm()
	}, [isOpen])

	return {
		values,
		setValue,
		handleSubmit,
		isLoading
	}
}

export default useCreateCategory
