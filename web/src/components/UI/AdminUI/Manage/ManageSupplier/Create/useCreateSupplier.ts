import { createSupplier, CreateSupplierInput, editSupplier, getSupplier } from "@api"
import { isEmail } from "@helper"
import { useChakraToast, useFormCore } from "@hooks"
import { ChangeEvent, FormEvent, useEffect, useState } from "react"
import { useMutation, useQuery, useQueryClient } from "react-query"

const useCreateSupplier = (id?: number) => {
	const toast = useChakraToast()
	const qc = useQueryClient()

	const [readOnly, setReadOnly] = useState(!!id)
	const [currentId, setCurrentId] = useState(id)
	const [confirmDelete, setConfirmDelete] = useState(false)

	useEffect(() => {
		setReadOnly(!!id)
		setCurrentId(id)
	}, [id])

	const formInfo = {
		code: {
			label: "Mã nhà cung cấp",
			required: false,
			placeholder: "Mã tự động"
		},
		name: {
			label: "Tên nhà cung cấp",
			required: true
		},
		phone: {
			label: "Số điện thoại",
			required: true
		},
		email: {
			label: "Email",
			required: false
		},
		address: {
			label: "Địa chỉ",
			required: false
		},
		note: {
			label: "Ghi chú",
			required: false
		},
		tax: {
			label: "Mã số thuế",
			required: false
		}
	}

	const { values, setValue, initForm } = useFormCore<CreateSupplierInput>({
		code: "",
		name: "",
		phone: "",
		email: "",
		address: "",
		note: "",
		tax: ""
	})

	const { refetch, data } = useQuery(["supplier", currentId], () => getSupplier(currentId!), {
		enabled: false,
		onSuccess: data => {
			initForm({
				code: data.code,
				name: data.name,
				phone: data.phone,
				email: data.email,
				address: data.address,
				note: data.note,
				tax: data.tax
			})
		}
	})

	useEffect(() => {
		if (readOnly) {
			console.log("Fetching supplier")
			refetch()
		}
	}, [readOnly])

	const validate = () => {
		if (!values.name) {
			toast({
				title: "Tên nhà cung cấp là bắt buộc",
				message: "Vui lòng nhập tên nhà cung cấp",
				status: "error"
			})
			return false
		}

		if (!values.phone) {
			toast({
				title: "Số điện thoại là bắt buộc",
				message: "Vui lòng nhập số điện thoại",
				status: "error"
			})
			return false
		}

		if (values.email && !isEmail(values.email)) {
			toast({
				title: "Email không hợp lệ",
				message: "Vui lòng nhập địa chỉ email hợp lệ",
				status: "error"
			})
			return false
		}
		return true
	}

	const { mutate: mutateCreateSupplier, isLoading: isCreatingSupplier } = useMutation(() => createSupplier(values), {
		onSuccess: data => {
			qc.invalidateQueries("suppliers")
			setCurrentId(data.id)
			setReadOnly(true)
			toast({
				title: "Thêm nhà cung cấp thành công",
				status: "success"
			})
		},
		onError: (e: any) => {
			toast({
				title: e.response.data.message || "Có lỗi xảy ra",
				message: e.response.data.error || "Vui lòng thử lại",
				status: "error"
			})
		}
	})

	const { mutate: mutateEditSupplier, isLoading: isEditingSupplier } = useMutation(() => editSupplier(currentId!, values), {
		onSuccess: () => {
			qc.invalidateQueries("suppliers")
			setReadOnly(true)
			toast({
				title: "Cập nhật nhà cung cấp thành công",
				status: "success"
			})
		},
		onError: (e: any) => {
			toast({
				title: e.response.data.message || "Có lỗi xảy ra",
				message: e.response.data.error || "Vui lòng thử lại",
				status: "error"
			})
		}
	})

	const formControlData = Object.keys(values).map(key => ({
		label: formInfo[key].label,
		value: values[key],
		onChange: (e: ChangeEvent<HTMLInputElement>) => setValue(key as keyof CreateSupplierInput, e.target.value),
		isRequired: formInfo[key].required,
		placeholder: formInfo[key].placeholder || "",
		isReadOnly: readOnly
	}))

	const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
		e.preventDefault()

		if (readOnly) {
			setReadOnly(false)
			return
		}

		if (validate()) {
			id ? mutateEditSupplier() : mutateCreateSupplier()
		}
	}

	const isLoading = isCreatingSupplier || isEditingSupplier

	return {
		formControlData,
		handleSubmit,
		isLoading,
		readOnly,
		setReadOnly,
		data,
		confirmDelete,
		setConfirmDelete,
		currentId
	}
}

export default useCreateSupplier
