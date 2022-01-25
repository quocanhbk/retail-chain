import { CreatePurchaseSheetInput, getItemsBySearch, Item, moveItem } from "@api"
import { useFormCore, useThrottle } from "@hooks"
import { useState } from "react"
import { useMutation, useQuery } from "react-query"

const useCreateImport = () => {
	const { mutate: mutateMoveItem } = useMutation(moveItem)
	const [searchText, setSearchText] = useState("")
	const throttledText = useThrottle(searchText, 1000)

	const searchQuery = useQuery(["getItemsBySearch", throttledText], () => getItemsBySearch(throttledText), {
		enabled: throttledText.length > 0
	})

	const { values, setValue } = useFormCore<CreatePurchaseSheetInput>({
		supplier_id: null,
		code: "",
		discount: 0,
		discount_type: "cash",
		items: [],
		note: ""
	})

	const handleClickDefaultItem = (item: Item) => {
		mutateMoveItem(item.barcode)
		handleClickItem(item)
	}

	const handleClickItem = (item: Item) => {
		// return if item is already in the list
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

	const mappedValues = values.items.map(item => ({
		...item,
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
		}
	}))

	return {
		searchText,
		setSearchText,
		searchQuery,
		handleClickDefaultItem,
		handleClickItem,
		values,
		mappedValues
	}
}

export default useCreateImport
