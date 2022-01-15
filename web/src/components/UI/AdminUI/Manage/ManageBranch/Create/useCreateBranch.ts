import { createBranch, CreateBranchInput } from "@api"
import { useChakraToast, useFormCore } from "@hooks"
import { useRouter } from "next/router"
import { FormEvent, useEffect, useRef, useState } from "react"
import { useMutation, useQueryClient } from "react-query"

const useCreateBranch = (id: number|undefined) => {
    const mode = id ? "edit" : "create"
	const [readOnly, setReadOnly] = useState(!!id)

    const { values, setValue, errors, setError } = useFormCore<CreateBranchInput>({
		name: "",
		address: "",
		image: null,
	})

	const toast = useChakraToast()
	const qc = useQueryClient()
	const inputRef = useRef<HTMLInputElement>(null)
    const router = useRouter()
    
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

    const mutationFn = 

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

    const { mutate, isLoading } = useMutation(() => editBranch(id, values), {
		onSuccess: () => {
			toast({
				title: "Chỉnh sửa chi nhánh thành công",
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

    return {
        values, setValue, errors, handleCreateBranch, isLoading, inputRef
    }
}

export default useCreateBranch