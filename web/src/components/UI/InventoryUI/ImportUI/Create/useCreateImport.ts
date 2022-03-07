import {
	createPurchaseSheet,
	CreatePurchaseSheetInput,
	deletePurchaseSheet,
	getPurchaseSheet,
	Item,
	moveItem,
	Supplier,
	updatePurchaseSheet
} from "@api"
import { useChakraToast, useFormCore } from "@hooks"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import { useMutation, useQuery } from "react-query"

const useCreateImport = (id?: number) => {
	const { mutateAsync: mutateMoveItem } = useMutation(moveItem)
	const toast = useChakraToast()
	const router = useRouter()

	const [readOnly, setReadOnly] = useState(!!id)

	// refetch data when readonly set to true
	useEffect(() => {
		if (readOnly) {
			refetch()
		}
	}, [readOnly])

	const {
		refetch,
		data,
		isLoading: isLoadingData
	} = useQuery(["getPurchaseSheet", id], () => getPurchaseSheet(id!), {
		enabled: false,
		onSuccess: data => {
			initForm({
				supplier_id: data.supplier_id,
				code: data.code,
				discount: data.discount,
				discount_type: data.discount_type,
				note: data.note,
				paid_amount: data.paid_amount,
				items: data.purchase_sheet_items.map(item => ({
					item_id: item.item_id,
					quantity: item.quantity,
					price: item.price,
					discount: item.discount,
					discount_type: item.discount_type,
					item: item.item
				}))
			})
			setSelectedSupplier(data.supplier)
		},
		onError: () => {
			toast({
				title: "Lấy dữ liệu thất bại",
				message: "Vui lòng thử lại sau",
				status: "error"
			})
		}
	})

	const { values, setValue, initForm } = useFormCore<CreatePurchaseSheetInput>({
		supplier_id: null,
		code: "",
		discount: 0,
		discount_type: "cash",
		items: [],
		note: "",
		paid_amount: 0
	})

	// used this state to keep track of selected supplier infomation
	const [selectedSupplier, setSelectedSupplier] = useState<Supplier | null>(null)

	const handleClickDefaultItem = (item: Item) => {
		mutateMoveItem(item.barcode).then(handleClickItem)
	}

	const handleClickItem = (item: Item) => {
		if (values.items.some(i => i.item_id === item.id)) return

		setValue("items", [
			...values.items,
			{
				item_id: item.id,
				quantity: 1,
				price: 0,
				discount: 0,
				discount_type: "cash",
				item
			}
		])
	}

	const handleConfirmButtonClick = () => {
		if (readOnly) {
			setReadOnly(false)
			return
		}

		if (!id) {
			mutateCreatePurchaseSheet()
			return
		}

		mutateUpdatePurchaseSheet()
	}

	const mappedItems = values.items.map(item => ({
		...item,
		discountValue: item.discount_type === "cash" ? item.discount : (item.discount / 100) * item.price,
		total: item.quantity * (item.price - (item.discount_type === "cash" ? item.discount : (item.discount / 100) * item.price)),
		onChangeQuantity: (quantity: number) => {
			setValue(
				"items",
				values.items.map(i => (i.item_id === item.item_id ? { ...i, quantity } : i))
			)
		},
		onChangePrice: (price: number) => {
			setValue(
				"items",
				values.items.map(i => (i.item_id === item.item_id ? { ...i, price } : i))
			)
		},
		onChangeDiscount: (discount: number) => {
			setValue(
				"items",
				values.items.map(i => (i.item_id === item.item_id ? { ...i, discount } : i))
			)
		},
		onChangeDiscountType: (discountType: string) => {
			setValue(
				"items",
				values.items.map(i => (i.item_id === item.item_id ? { ...i, discount: 0, discount_type: discountType } : i))
			)
		},
		onRemove: () => {
			setValue(
				"items",
				values.items.filter(i => i.item_id !== item.item_id)
			)
		}
	}))

	const total = mappedItems.reduce((acc, item) => acc + item.total, 0)

	const needToPay = values.discount_type === "cash" ? total - values.discount : (total * (100 - values.discount)) / 100

	useEffect(() => {
		setValue("paid_amount", needToPay)
	}, [needToPay])

	const { mutate: mutateCreatePurchaseSheet, isLoading: isCreatingPurchaseSheet } = useMutation(
		() => createPurchaseSheet({ ...values, supplier_id: selectedSupplier?.id || null }),
		{
			onSuccess: () => {
				toast({
					title: "Tạo phiếu nhập thành công",
					status: "success"
				})
				router.push("/main/inventory/import")
			},
			onError: (error: any) => {
				toast({
					status: "error",
					title: error.response.data.message,
					message: error.response.data.error
				})
			}
		}
	)

	const { mutate: mutateUpdatePurchaseSheet, isLoading: isUpdatingPurchaseSheet } = useMutation(() => updatePurchaseSheet(id!, values), {
		onSuccess: () => {
			toast({
				title: "Cập nhật phiếu nhập thành công",
				status: "success"
			})
			setReadOnly(true)
		}
	})

	const { mutate: mutateDeletePurchaseSheet, isLoading: isDeletingPurchaseSheet } = useMutation(() => deletePurchaseSheet(id!), {
		onSuccess: () => {
			toast({
				title: "Xóa phiếu nhập thành công",
				status: "success"
			})
			router.push("/main/inventory/import")
		},
		onError: () => {
			toast({
				title: "Xóa phiếu nhập thất bại",
				message: "Vui lòng thử lại sau",
				status: "error"
			})
		}
	})

	const [confirmDelete, setConfirmDelete] = useState(false)

	const isLoading = isCreatingPurchaseSheet || isUpdatingPurchaseSheet

	return {
		handleClickDefaultItem,
		handleClickItem,
		values,
		mappedItems,
		selectedSupplier,
		setSelectedSupplier,
		total,
		needToPay,
		setValue,
		handleConfirmButtonClick,
		isLoading,
		readOnly,
		setReadOnly,
		mutateDeletePurchaseSheet,
		isDeletingPurchaseSheet,
		confirmDelete,
		setConfirmDelete,
		data,
		isLoadingData
	}
}

export default useCreateImport
