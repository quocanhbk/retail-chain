import { Category, getCategories } from "@api"
import {
	InputGroup,
	InputLeftElement,
	Input,
	Box,
	ScaleFade,
	Spinner,
	Flex,
	Text,
	VStack,
	Button,
	InputRightElement,
	IconButton
} from "@chakra-ui/react"
import { useClickOutside, useThrottle } from "@hooks"
import { ChangeEvent, useState } from "react"
import { BsPlus, BsSearch, BsX } from "react-icons/bs"
import { BiCategoryAlt } from "react-icons/bi"
import { useQuery } from "react-query"
import CreateCategoryModal from "./CreateCategory/CreateCaegoryModal"

interface CategorySearchInputProps {
	selectedCategory: Category | null
	onSelectCategory: (category: Category | null) => void
}

export const CategorySearchInput = ({ selectedCategory, onSelectCategory }: CategorySearchInputProps) => {
	const [isOpen, setIsOpen] = useState(false)
	const [searchText, setSearchText] = useState("")
	const thottleSearchText = useThrottle(searchText, 500)
	const { data: categories, isLoading, isError } = useQuery(["categories", thottleSearchText], () => getCategories(thottleSearchText))

	const boxRef = useClickOutside<HTMLDivElement>(() => setIsOpen(false))

	const [isModalOpen, setIsModalOpen] = useState(false)

	const handleSelectCategory = (category: Category) => {
		onSelectCategory(category)
		setSearchText("")
	}

	const renderItems = () => {
		if (isLoading)
			return (
				<Flex align="center">
					<Spinner />
					<Text ml={2}>{"Đang tải"}</Text>
				</Flex>
			)

		if (isError || !categories) return <Text>{"Đã xảy ra lỗi, vui lòng thử lại sau"}</Text>

		if (categories.length === 0)
			return (
				<Flex align="center">
					<Text>{"Không tìm thấy danh mục"}</Text>
					<Button size="sm" ml={4} onClick={() => setIsModalOpen(true)}>
						{"Tạo mới"}
					</Button>
				</Flex>
			)

		return (
			<VStack align="stretch" maxH="15rem">
				{categories.map(category => (
					<Box
						key={category.id}
						py={1}
						px={2}
						_hover={{ bg: "background.third" }}
						cursor="pointer"
						rounded="md"
						onClick={() => handleSelectCategory(category)}
					>
						<Text key={category.id}>{category.name}</Text>
					</Box>
				))}
			</VStack>
		)
	}

	const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
		if (selectedCategory) return
		setSearchText(e.target.value)
	}

	return (
		<Box pos="relative" ref={boxRef}>
			<InputGroup>
				<InputLeftElement>{selectedCategory ? <BiCategoryAlt /> : <BsSearch />}</InputLeftElement>
				<Input
					w="full"
					value={selectedCategory ? selectedCategory.name : searchText}
					background={"background.secondary"}
					onChange={handleChange}
					placeholder="Tìm kiếm danh mục"
					onFocus={() => !selectedCategory && setIsOpen(true)}
					readOnly={!!selectedCategory}
					pr="3rem"
				/>
				<InputRightElement width="3rem">
					<IconButton
						aria-label="add category"
						icon={selectedCategory ? <BsX size="1.25rem" /> : <BsPlus size="1.25rem" />}
						size="sm"
						onClick={() => (selectedCategory ? onSelectCategory(null) : setIsModalOpen(true))}
						rounded="full"
						variant={"ghost"}
						colorScheme={"gray"}
					/>
				</InputRightElement>
			</InputGroup>
			<Box pos="absolute" top="100%" left={0} w="full" transform="translateY(0.5rem)" zIndex="dropdown">
				<ScaleFade in={isOpen && searchText.length > 0} unmountOnExit>
					<Box p={2} rounded="md" border="1px" background={backgroundPrimary} borderColor={"border.primary"} overflow={"auto"}>
						{renderItems()}
					</Box>
				</ScaleFade>
			</Box>
			<CreateCategoryModal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} onSelectCategory={onSelectCategory} />
		</Box>
	)
}

export default CategorySearchInput
