import { getSuppliers } from "@api"
import { InputGroup, InputLeftElement, Input, Box, ScaleFade, Spinner, Flex, Text, VStack, Button } from "@chakra-ui/react"
import { useClickOutside, useTheme, useThrottle } from "@hooks"
import { useState } from "react"
import { BsSearch } from "react-icons/bs"
import { useQuery } from "react-query"

export const SupplierSearchInput = () => {
	const { backgroundSecondary, backgroundPrimary, borderPrimary } = useTheme()
	const [isOpen, setIsOpen] = useState(false)

	const [searchText, setSearchText] = useState("")
	const thottleSearchText = useThrottle(searchText, 500)
	const { data: suppliers, isLoading, isError } = useQuery(["suppliers", thottleSearchText], () => getSuppliers(thottleSearchText))

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
					<Button colorScheme={"green"} size="sm" ml={4}>
						{"Tạo mới"}
					</Button>
				</Flex>
			)

		return (
			<VStack align="stretch" maxH="15rem">
				{suppliers.map(supplier => (
					<Text key={supplier.id}>{supplier.name}</Text>
				))}
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
					background={backgroundSecondary}
					onChange={e => setSearchText(e.target.value)}
					placeholder="Tìm kiếm nhà cung cấp"
					onFocus={() => setIsOpen(true)}
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

export default SupplierSearchInput
