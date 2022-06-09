import {
	createReturnPurchaseSheet,
	CreateReturnPurchaseSheetInput,
	getPurchaseSheet,
	getReturnPurchaseSheet,
	updateReturnPurchaseSheet
} from "@api"
import { useChakraToast, useFormCore } from "@hooks"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import { useMutation, useQuery } from "react-query"

const useCreateReturnImport = (id?: number) => {
	const toast = useChakraToast()
	const router = useRouter()
	const [readOnly, setReadOnly] = useState(false)
	useEffect(() => {
		if (router.isReady) setReadOnly(!!id)
	}, [router.isReady])

	const purchaseSheetId = parseInt(router.query["purchase-sheet"] as string) || null
	const [notFound, setNotFound] = useState(false)

	const purchaseSheetQuery = useQuery(["purchase_sheet", purchaseSheetId], () => getPurchaseSheet(purchaseSheetId!), {
		enabled: !!purchaseSheetId,
		onError: () => setNotFound(true),
		onSuccess: data => {
			initForm({
				supplier_id: data.supplier_id,
				code: "",
				discount: 0,
				discount_type: "cash",
				note: "",
				paid_amount: 0,
				items: data.purchase_sheet_items.map(item => ({
					item_id: item.item_id,
					quantity: 0,
					price: item.price,
					return_price: item.price,
					return_price_type: "cash",
					item: item.item
				})),
				purchase_sheet_id: purchaseSheetId!
			})
		}
	})

	const {
		refetch,
		data,
		isLoading: isLoadingData
	} = useQuery(["return-purchase-sheet", id], () => getReturnPurchaseSheet(id!), {
		enabled: false,
		onSuccess: data => {
			console.log(data)
			initForm({
				supplier_id: data.supplier_id,
				code: data.code,
				discount: data.discount,
				discount_type: data.discount_type,
				note: data.note,
				paid_amount: data.paid_amount,
				items: data.return_purchase_sheet_items.map(item => ({
					item_id: item.item_id,
					quantity: item.quantity,
					price: item.price,
					return_price: item.return_price,
					return_price_type: item.return_price_type,
					item: item.item
				})),
				purchase_sheet_id: 0
			})
		},
		onError: () => {
			toast({
				title: "Lấy dữ liệu thất bại",
				message: "Vui lòng thử lại sau",
				status: "error"
			})
		}
	})

	// refetch data when readonly set to true
	useEffect(() => {
		console.log(readOnly)
		if (readOnly) {
			console.log("fetch")
			refetch()
		}
	}, [readOnly])

	const { values, setValue, initForm } = useFormCore<CreateReturnPurchaseSheetInput>({
		supplier_id: null,
		code: "",
		discount: 0,
		discount_type: "cash",
		items: [],
		note: "",
		paid_amount: 0,
		purchase_sheet_id: 0
	})

	const handleConfirmButtonClick = () => {
		mutateCreate()
	}

	const mappedItems = values.items.map(item => ({
		...item,
		total: (item.price - (item.return_price_type === "cash" ? item.return_price : (item.return_price / 100) * item.price)) * item.quantity,
		onChangeQuantity: (quantity: number) => {
			setValue(
				"items",
				values.items.map(i => (i.item_id === item.item_id ? { ...i, quantity } : i))
			)
		},
		onChangeReturnPrice: (return_price: number) => {
			setValue(
				"items",
				values.items.map(i => (i.item_id === item.item_id ? { ...i, return_price } : i))
			)
		},
		onChangeReturnPriceTye: (return_price_type: string) => {
			setValue(
				"items",
				values.items.map(i => (i.item_id === item.item_id ? { ...i, return_price: 0, return_price_type } : i))
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

	const { mutate: mutateCreate, isLoading: isCreating } = useMutation(
		() =>
			createReturnPurchaseSheet({
				...values,
				supplier_id: purchaseSheetQuery.data?.supplier?.id || null,
				purchase_sheet_id: purchaseSheetId as number
			}),
		{
			onSuccess: () => {
				toast({
					title: "Tạo phiếu trả hàng nhập thành công",
					status: "success"
				})
				router.push("/main/inventory/return-import")
			}
		}
	)

	const { mutate: mutateUpdate, isLoading: isUpdating } = useMutation(() => updateReturnPurchaseSheet(id!, values), {
		onSuccess: () => {
			toast({
				title: "Cập nhật phiếu trả hàng nhập thành công",
				status: "success"
			})
			setReadOnly(true)
		}
	})

	const isLoading = isCreating || isUpdating

	return {
		mappedItems,
		readOnly,
		data,
		isLoadingData: isLoadingData || purchaseSheetQuery.isLoading,
		purchaseSheetQuery,
		values,
		setValue,
		total,
		needToPay,
		handleConfirmButtonClick,
		isLoading,
		notFound
	}
}

export default useCreateReturnImport
