import { createSupplier, CreateSupplierInput, Supplier } from "@api"
import { isEmail } from "@helper"
import { useChakraToast, useFormCore } from "@hooks"
import { ChangeEvent, FormEvent, useEffect } from "react"
import { useMutation, useQueryClient } from "react-query"

const useCreateSupplier = (isOpen: boolean, onClose: () => void, onSelectSupplier: (supplier: Supplier | null) => void) => {
	const toast = useChakraToast()
	const qc = useQueryClient()

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

	const { mutate: mutateCreateSupplier, isLoading } = useMutation(() => createSupplier(values), {
		onSuccess: data => {
			qc.invalidateQueries("suppliers")
			onSelectSupplier(data)
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

	const formControlData = Object.keys(values).map(key => ({
		label: formInfo[key].label,
		value: values[key],
		onChange: (e: ChangeEvent<HTMLInputElement>) => setValue(key as keyof CreateSupplierInput, e.target.value),
		isRequired: formInfo[key].required,
		placeholder: formInfo[key].placeholder || ""
	}))

	const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
		e.preventDefault()
		if (validate()) {
			mutateCreateSupplier()
		}
	}

	useEffect(() => {
		initForm()
	}, [isOpen])

	return {
		formControlData,
		handleSubmit,
		isLoading
	}
}

export default useCreateSupplier
