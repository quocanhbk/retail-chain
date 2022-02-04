import { useMutation, useQuery } from "react-query"
import { Category, createItem, CreateProductInput, getItems, getItemsBySearch, Item } from "@api"
import { useChakraToast, useFormCore, useThrottle } from "@hooks"
import { useState } from "react"

const useProductHome = () => {
	const [search, setSearch] = useState("")
	const [searchText, setSearchText] = useState("")
	const toast = useChakraToast()
	const [selectedCategory, setSelectedCategory] = useState<Category | null>(null)

	const allItemQuery = useQuery("items", getItems)
	const throttledTextItemCurrent = useThrottle(search, 1000)
	const searchQueryCurrent = useQuery(
		["getItemsBySearchCurrent", throttledTextItemCurrent],
		() => getItemsBySearch(throttledTextItemCurrent),
		{
			enabled: throttledTextItemCurrent.length > 0
		}
	)

	const [isClose, setIsClose] = useState(true)
	const { values, setValue, initForm } = useFormCore<CreateProductInput>({
		code: "",
		name: "",
		barcode: "",
		image: "",
		category_id: 0,
		base_price: 0,
		sell_price: 0,
		quantily: 0
	})
	const throttledText = useThrottle(
		searchText !== values.name ? values.name : searchText !== values.barcode ? values.barcode : searchText,
		1000
	)
	const searchQuery = useQuery(["getItemsBySearch", throttledText], () => getItemsBySearch(throttledText), {
		enabled: throttledText.length > 0
	})

	const handleClickDefaultItem = (item: Item) => {
		handleClickItem(item)
	}

	const handleClickItem = (item: Item) => {
		setValue("code", item.code)
		setValue("name", item.name)
		setValue("barcode", item.barcode)
		setValue("image", item.image)
	}

	const { mutate: mutateCreateItem, isLoading } = useMutation(() => createItem({ ...values, category_id: selectedCategory?.id || 0 }), {
		onSuccess: () => {
			toast({
				title: "Tạo san pham thanh cong",
				status: "success"
			})
			setIsClose(true)
		},
		onError: (err: any) => {
			console.log(err.response.data.message)

			toast({
				title: err.response.data.message,
				status: "error"
			})
		}
	})

	return {
		values,
		setValue,
		initForm,
		allItemQuery,
		search,
		setSearch,
		searchText,
		setSearchText,
		searchQuery,
		handleClickDefaultItem,
		handleClickItem,
		isClose,
		setIsClose,
		selectedCategory,
		setSelectedCategory,
		isLoading,
		mutateCreateItem,
		searchQueryCurrent
	}
}

export default useProductHome
