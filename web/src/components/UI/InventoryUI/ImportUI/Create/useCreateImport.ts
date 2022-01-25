import { createPurchaseSheet, CreatePurchaseSheetInput, getItemsBySearch, Item, moveItem, Supplier } from "@api"
import { toast } from "@chakra-ui/react"
import { useChakraToast, useFormCore, useThrottle } from "@hooks"
import { useRouter } from "next/router"
import { useEffect, useState } from "react"
import { useMutation, useQuery } from "react-query"

const useCreateImport = () => {
	const { mutate: mutateMoveItem } = useMutation(moveItem)
	const [searchText, setSearchText] = useState("")
	const throttledText = useThrottle(searchText, 1000)
	const toast = useChakraToast()
	const router = useRouter()
	const searchQuery = useQuery(["getItemsBySearch", throttledText], () => getItemsBySearch(throttledText), {
		enabled: throttledText.length > 0
	})

	const { values, setValue } = useFormCore<CreatePurchaseSheetInput>({
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
		mutateMoveItem(item.barcode)
		handleClickItem(item)
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
		}
	}))

	const total = mappedItems.reduce((acc, item) => acc + item.total, 0)

	const needToPay = values.discount_type === "cash" ? total - values.discount : (total * (100 - values.discount)) / 100

	useEffect(() => {
		setValue("paid_amount", needToPay)
	}, [needToPay])

	const { mutate: mutateCreatePurchaseSheet, isLoading } = useMutation(
		() => createPurchaseSheet({ ...values, supplier_id: selectedSupplier?.id || null }),
		{
			onSuccess: () => {
				toast({
					title: "Tạo phiếu nhập thành công",
					status: "success"
				})
				router.push("/main/inventory/import")
			}
		}
	)

	return {
		searchText,
		setSearchText,
		searchQuery,
		handleClickDefaultItem,
		handleClickItem,
		values,
		mappedItems,
		selectedSupplier,
		setSelectedSupplier,
		total,
		needToPay,
		setValue,
		mutateCreatePurchaseSheet,
		isLoading
	}
}

export default useCreateImport
