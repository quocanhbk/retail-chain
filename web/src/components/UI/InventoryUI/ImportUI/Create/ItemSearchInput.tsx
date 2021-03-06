import { Item } from "@api"
import {
	InputGroup,
	InputLeftElement,
	Input,
	InputRightElement,
	IconButton,
	Box,
	ScaleFade,
	Spinner,
	Flex,
	Text,
	VStack,
	Button
} from "@chakra-ui/react"
import { useClickOutside } from "@hooks"
import { useState } from "react"
import { BsSearch } from "react-icons/bs"
import SearchItem from "./SearchItem"
import useSearchQuery from "./useSearchQuery"

interface ItemSearchInputProps {
	searchText: string
	setSearchText: (text: string) => void
	searchQuery: ReturnType<typeof useSearchQuery>["searchQuery"]
	onDefaultItemClick: (item: Item) => void
	onItemClick: (item: Item) => void
}

export const ItemSearchInput = ({
	searchText,
	setSearchText,
	searchQuery: { isLoading, isError, data },
	onDefaultItemClick,
	onItemClick
}: ItemSearchInputProps) => {
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
					<Button colorScheme={"green"} size="sm" ml={4}>
						{"Tạo mới"}
					</Button>
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
							setSearchText("")
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
										setSearchText("")
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
				<InputLeftElement>
					<BsSearch />
				</InputLeftElement>
				<Input
					w="full"
					value={searchText}
					background={"background.secondary"}
					onChange={e => setSearchText(e.target.value)}
					placeholder="Tìm kiếm sản phẩm"
					onFocus={() => setIsOpen(true)}
				/>
			</InputGroup>
			<Box pos="absolute" top="100%" left={0} w="full" transform="translateY(0.5rem)" zIndex="dropdown">
				<ScaleFade in={isOpen && searchText.length > 0} unmountOnExit>
					<Box p={4} rounded="md" border="1px" background={"background.primary"} borderColor={"border.primary"} overflow={"auto"}>
						{renderItems()}
					</Box>
				</ScaleFade>
			</Box>
		</Box>
	)
}

export default ItemSearchInput
