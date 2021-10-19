import { Flex, Text } from "@chakra-ui/react"
import { useState } from "react"
import CategoryUI from "./CategoryUI"

interface InventoryUIProps {}

const inventoryMenus = [{ id: "category", name: "Danh mục sản phẩm" }] as const

const InventoryUI = ({}: InventoryUIProps) => {
	const [currentPage, setCurrentPage] = useState<typeof inventoryMenus[number]["id"]>("category")
	return (
		<Flex w="full" h="full">
			<Flex direction="column" bg="gray.700" color="white" p={2} w="20rem">
				{inventoryMenus.map(menu => (
					<Text
						key={menu.id}
						cursor="pointer"
						w="full"
						textAlign="center"
						p={2}
						letterSpacing="1px"
						color={currentPage === menu.id ? "white" : "whiteAlpha.700"}
						fontWeight="semibold"
					>
						{menu.name}
					</Text>
				))}
			</Flex>
			<Flex flex={1}>{currentPage === "category" ? <CategoryUI /> : null}</Flex>
		</Flex>
	)
}

export default InventoryUI
