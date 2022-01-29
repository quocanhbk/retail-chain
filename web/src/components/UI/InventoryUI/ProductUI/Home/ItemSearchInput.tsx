import { CreateProductInput, Item } from "@api"
import {
	InputGroup,
	Input,
	Box,
	ScaleFade,
	Spinner,
	Flex,
	Text,
	VStack,
	Button
} from "@chakra-ui/react"
import { useClickOutside, useTheme } from "@hooks"
import { useEffect, useState } from "react"
import SearchItem from "./SearchItem"
import useProductHome from "./useProductHome"


interface ItemSearchInputProps {
	type: string

	searchText: string
	setSearchText: (field: keyof CreateProductInput, text: string) => void
	searchQuery: ReturnType<typeof useProductHome>["searchQuery"]
	onDefaultItemClick: (item: Item) => void
	onItemClick: (item: Item) => void
}

export const ItemSearchInput = ({
	type,

	searchText,
	setSearchText,
	searchQuery: { isLoading, isError, data },
	onDefaultItemClick,
	onItemClick
}: ItemSearchInputProps) => {
	const { backgroundSecondary, backgroundPrimary, borderPrimary } = useTheme()
	const [isOpen, setIsOpen] = useState(false)

	const renderItems = () => {
		if (isLoading)
			return (
				<Flex align="center">
					<Spinner />
					<Text ml={2}>{"Đang tải"}</Text>
				</Flex>
			)

		if (isError) return <Text>{"Đã xảy ra lỗi, vui lòng thử lại sau"}</Text>

		if (!data || (data.currentItems.length === 0 && data.defaultItems.length === 0))
			return (
				<Flex align="center">
					<Text>{"Không tìm thấy sản phẩm"}</Text>
				</Flex>
			)
		
		return (
			<VStack align="stretch" maxH="15rem">
				{data.currentItems.map(item => (
					<SearchItem
						key={item.id}
						item={item}
						onClick={() => {
							onItemClick(item)
							setIsOpen(false)
						}}
					/>
				))}
				{data.defaultItems.length > 0 && (
					<>
						<Text>{"Sản phẩm mới"}</Text>
						<Box>
							{data.defaultItems.map(item => (
								<SearchItem
									key={item.id}
									item={item}
									onClick={() => {
										onDefaultItemClick(item)
										setIsOpen(false)
									}}
								/>
							))}
						</Box>
					</>
				)}
			</VStack>
		)
	}

	const boxRef = useClickOutside<HTMLDivElement>(() => setIsOpen(false))

	return (
		<Box pos="relative" ref={boxRef}>
			<InputGroup>
				<Input
					w="full"
					value={searchText}
					background={backgroundSecondary}
					onChange={e => setSearchText(type === "name" ? "name" : "barcode",e.target.value)}
					onFocus={() => setIsOpen(true)}
					isRequired = {true}
				/>
			</InputGroup>
			<Box pos="absolute" top="100%" left={0} w="full" transform="translateY(0.5rem)" zIndex="dropdown">
				<ScaleFade in={isOpen && searchText.length > 0} unmountOnExit>
					<Box p={4} rounded="md" border="1px" background={backgroundPrimary} borderColor={borderPrimary} overflow={"auto"}>
						{renderItems()}
					</Box>
				</ScaleFade>
			</Box>
		</Box>
	)
}

export default ItemSearchInput