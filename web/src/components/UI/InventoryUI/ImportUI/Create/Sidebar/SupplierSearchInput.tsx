import { getSuppliers, Supplier } from "@api"
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
import { useClickOutside, useTheme, useThrottle } from "@hooks"
import { ChangeEvent, useState } from "react"
import { BsPlus, BsSearch, BsTruck, BsX } from "react-icons/bs"
import { useQuery } from "react-query"
import CreateSupplierModal from "./CreateSupplierModal"

interface SupplierSearchInputProps {
	selectedSupplier: Supplier | null
	onSelectSupplier: (supplier: Supplier | null) => void
	readOnly: boolean
}

export const SupplierSearchInput = ({ selectedSupplier, onSelectSupplier, readOnly }: SupplierSearchInputProps) => {
	const { backgroundSecondary, backgroundPrimary, borderPrimary, backgroundThird } = useTheme()

	const [isOpen, setIsOpen] = useState(false)

	const [searchText, setSearchText] = useState("")

	const thottleSearchText = useThrottle(searchText, 500)

	const { data: suppliers, isLoading, isError } = useQuery(["suppliers", thottleSearchText], () => getSuppliers(thottleSearchText))

	const boxRef = useClickOutside<HTMLDivElement>(() => setIsOpen(false))

	const [isModalOpen, setIsModalOpen] = useState(false)

	const handleSelectSupplier = (supplier: Supplier) => {
		onSelectSupplier(supplier)
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

		if (isError || !suppliers) return <Text>{"Đã xảy ra lỗi, vui lòng thử lại sau"}</Text>

		if (suppliers.length === 0)
			return (
				<Flex align="center">
					<Text>{"Không tìm thấy nhà cung cấp"}</Text>
					<Button size="sm" ml={4} onClick={() => setIsModalOpen(true)}>
						{"Tạo mới"}
					</Button>
				</Flex>
			)

		return (
			<VStack align="stretch" maxH="15rem">
				{suppliers.map(supplier => (
					<Box
						key={supplier.id}
						py={1}
						px={2}
						_hover={{ bg: backgroundThird }}
						cursor="pointer"
						rounded="md"
						onClick={() => handleSelectSupplier(supplier)}
					>
						<Text key={supplier.id}>{supplier.name}</Text>
					</Box>
				))}
			</VStack>
		)
	}

	const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
		if (selectedSupplier) return
		setSearchText(e.target.value)
	}

	return (
		<Box pos="relative" ref={boxRef}>
			<InputGroup>
				<InputLeftElement>{selectedSupplier ? <BsTruck /> : <BsSearch />}</InputLeftElement>
				<Input
					w="full"
					value={selectedSupplier ? `${selectedSupplier.name} - ${selectedSupplier.code}` : searchText}
					background={backgroundSecondary}
					onChange={handleChange}
					placeholder="Tìm kiếm nhà cung cấp"
					onFocus={() => !selectedSupplier && setIsOpen(true)}
					readOnly={!!selectedSupplier}
					pr="3rem"
				/>
				{!readOnly && (
					<InputRightElement width="3rem">
						<IconButton
							aria-label="add supplier"
							icon={selectedSupplier ? <BsX size="1.25rem" /> : <BsPlus size="1.25rem" />}
							size="sm"
							onClick={() => (selectedSupplier ? onSelectSupplier(null) : setIsModalOpen(true))}
							rounded="full"
							variant={"ghost"}
							colorScheme={"gray"}
						/>
					</InputRightElement>
				)}
			</InputGroup>
			<Box pos="absolute" top="100%" left={0} w="full" transform="translateY(0.5rem)" zIndex="dropdown">
				<ScaleFade in={isOpen && searchText.length > 0} unmountOnExit>
					<Box p={2} rounded="md" border="1px" background={backgroundPrimary} borderColor={borderPrimary} overflow={"auto"}>
						{renderItems()}
					</Box>
				</ScaleFade>
			</Box>
			<CreateSupplierModal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} onSelectSupplier={onSelectSupplier} />
		</Box>
	)
}

export default SupplierSearchInput
