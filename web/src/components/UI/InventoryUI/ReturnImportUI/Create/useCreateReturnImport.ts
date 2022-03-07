import {
	createReturnPurchaseSheet,
	CreateReturnPurchaseSheetInput,
	getPurchaseSheet,
	getReturnableItems,
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

	const [readOnly, setReadOnly] = useState(!!id)

	const purchaseSheetId = parseInt(router.query["return-purchase-sheet"] as string) || null
	const [notFound, setNotFound] = useState(!!purchaseSheetId)

	useEffect(() => {
		if (router.isReady) {
			const purchaseSheetId = parseInt(router.query["return-purchase-sheet"] as string) || null
			setNotFound(!!purchaseSheetId)
		}
	}, [router.isReady])

	// refetch data when readonly set to true
	useEffect(() => {
		if (readOnly) {
			refetch()
		}
	}, [readOnly])

	const purchaseSheetQuery = useQuery(["purchase_sheet", purchaseSheetId], () => getPurchaseSheet(purchaseSheetId!), {
		enabled: !!purchaseSheetId,
		onError: () => setNotFound(true)
	})

	const [fetched, setFetched] = useState(false)
	const {data: returnableItems}  = useQuery(["returnable-items", purchaseSheetId], () => getReturnableItems(purchaseSheetId!), {
		enabled: !!purchaseSheetId && !fetched,
		onSuccess: data => {
			setValue(
				"items",
				data.map(returnableItem => ({
					item_id: returnableItem.id,
					quantity: 0,
					return_price: 0,
					return_price_type: "cash",
					item: returnableItem
				}))
			)
			setFetched(true)
		}
	})

	const {
		refetch,
		data,
		isLoading: isLoadingData
	} = useQuery(["return-purchase-sheet", id], () => getReturnPurchaseSheet(id!), {
		enabled: false,
		onSuccess: data => {
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
		if (readOnly) {
			setReadOnly(false)
			return
		}

		if (!id) mutateCreate()
		else mutateUpdate()
	}

	const getPurchasePrice = (itemId: number) => returnableItems?.find(item => item.id === itemId)?.base_price || 0

	const mappedItems = values.items.map(item => ({
		...item,
		// returnPrice: item.return_price_type === "cash" ? item.return_price : (item.return_price / 100) * item.price,
		total: (getPurchasePrice(item.item_id) - (item.return_price_type === "cash" ? item.return_price : (item.return_price / 100) * item.price)) * item.quantity,
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
