import { Button, chakra, Flex, HStack, Input } from "@chakra-ui/react"
import { FormControl } from "@components/shared"
import { ChakraModal } from "@components/shared"
import { useEffect, useRef } from "react"
import CategorySearchInput from "./CategorySearchInput"
import ImageProductInput from "./ImageProductInput"
import ItemSearchInput from "./ItemSearchInput"
import useProductHome from "./useProductHome"

interface AddNewProductModalProps {
	isOpen: boolean
	onClose: () => void
}
const AddNewProductModal = ({ isOpen, onClose }: AddNewProductModalProps) => {
	const {
		values,
		setValue,
		initForm,
		searchQuery,
		handleClickDefaultItem,
		handleClickItem,
		selectedCategory,
		setSelectedCategory,
		isLoading,
		mutateCreateItem
	} = useProductHome()

	const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
		e.preventDefault()
		// if (!validate()) {
		// 	return
		// }
		mutateCreateItem()
	}

	useEffect(() => {
		if (isOpen) initForm()
	}, [isOpen])

	const inputRef = useRef<HTMLInputElement>(null)

	return (
		<ChakraModal title="Thêm hàng hóa" isOpen={isOpen} onClose={onClose} size="6xl" initialFocusRef={inputRef}>
			<chakra.form onSubmit={handleSubmit} noValidate>
				<FormControl label="Mã hàng hóa" mb={4} isRequired={false}>
					<Input
						value={values.code}
						bg={"background.secondary"}
						onChange={e => setValue("code", e.target.value)}
						placeholder="Mã tư động"
						ref={inputRef}
					/>
				</FormControl>

				<FormControl label="Mã vạch" mb={4} isRequired={true}>
					<ItemSearchInput
						type="barcode"
						searchText={values.barcode}
						setSearchText={setValue}
						searchQuery={searchQuery}
						onDefaultItemClick={handleClickDefaultItem}
						onItemClick={handleClickItem}
					/>
				</FormControl>
				<FormControl label="Tên hàng hóa" mb={4} isRequired={true}>
					<ItemSearchInput
						type="name"
						searchText={values.name}
						setSearchText={setValue}
						searchQuery={searchQuery}
						onDefaultItemClick={handleClickDefaultItem}
						onItemClick={handleClickItem}
					/>
				</FormControl>
				<FormControl label="Danh mục" mb={4} isRequired={true}>
					<CategorySearchInput selectedCategory={selectedCategory} onSelectCategory={setSelectedCategory} />
				</FormControl>
				<ImageProductInput file={values.image} onSubmit={f => setValue("image", f)} />
				<Flex>
					<FormControl label="Giá vốn" mb={4} isRequired={true} maxW={"15rem"} mr={4}>
						<Input
							value={values.base_price}
							onChange={e => setValue("base_price", e.target.value)}
							type="number"
							textAlign={"right"}
						/>
					</FormControl>
					<FormControl label="Giá bán" mb={4} isRequired={true} maxW={"15rem"} mr={4}>
						<Input
							value={values.sell_price}
							onChange={e => setValue("sell_price", e.target.value)}
							type="number"
							textAlign={"right"}
						/>
					</FormControl>
					<FormControl label="Tồn kho" mb={4} isRequired={true} maxW={"15rem"} mr={4}>
						<Input value={values.quantily} onChange={e => setValue("quantily", e.target.value)} type="number" textAlign={"right"} />
					</FormControl>
				</Flex>
				<HStack my={6} w="full" justify={"flex-end"} spacing={4}>
					<Button type="submit" isLoading={isLoading}>
						{"Xác nhận"}
					</Button>
					<Button variant="ghost" onClick={onClose} w="6rem">
						Hủy
					</Button>
				</HStack>
			</chakra.form>
		</ChakraModal>
	)
}

export default AddNewProductModal
