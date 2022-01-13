// import dataFetcher from "@api"
import { Box, Flex, Heading, Wrap, WrapItem, Text } from "@chakra-ui/react"
import { useState } from "react"
import { BsPlus, BsX } from "react-icons/bs"
import { useQuery } from "react-query"
import AddCategoryModal from "./AddCategoryModal"
import DeleteCategoryModal from "./DeleteCategoryModal"

interface CategoryUIProps {}

const CategoryUI = ({}: CategoryUIProps) => {
	const [isOpen, setIsOpen] = useState(false)
	const [deletingId, setDeletingId] = useState<number | null>(null)
	// const { data } = useQuery("category", () => dataFetcher.getCategory())

	return (
		<Flex direction="column" w="full" p={4}>
			<Box mb={4}>
				<Heading>{"Danh mục sản phẩm"}</Heading>
			</Box>
			<Flex flex={1} justify="center" w="full">
				{/* <Wrap w="full">
					{data?.map(category => (
						<WrapItem
							key={category.id}
							px={4}
							py={2}
							bg="gray.800"
							color="white"
							rounded="full"
							_hover={{ bg: "gray.900" }}
							cursor="pointer"
						>
							<Text size="small">{category.name}</Text>
							<Box ml={2} onClick={() => setDeletingId(category.id)}>
								<BsX size="1.2rem" />
							</Box>
						</WrapItem>
					))}
					<WrapItem
						px={4}
						py={2}
						bg="red.500"
						color="white"
						rounded="full"
						_hover={{ bg: "red.600" }}
						_active={{ bg: "red.700" }}
						cursor="pointer"
						onClick={() => setIsOpen(true)}
						alignItems="center"
					>
						<Text size="small" userSelect="none">
							{"Thêm danh mục"}
						</Text>
						<Box ml={2}>
							<BsPlus size="1.2rem" />
						</Box>
					</WrapItem>
				</Wrap> */}
				<AddCategoryModal isOpen={isOpen} onClose={() => setIsOpen(false)} />
				{/* <DeleteCategoryModal
					deletingId={deletingId}
					setDeletingId={setDeletingId}
					categoryName={data?.find(cate => cate.id === deletingId)?.name}
				/> */}
			</Flex>
		</Flex>
	)
}

export default CategoryUI
