import { Box, Collapse, VStack, Text, Button } from "@chakra-ui/react"
import { useClickOutside } from "@hooks"
import { useState } from "react"

interface AddEmployeeButtonProps {
	setIsCreatingEmployee: (isCreatingEmployee: "create" | "transfer" | null) => void
}

const AddEmployeeButton = ({ setIsCreatingEmployee }: AddEmployeeButtonProps) => {
	const [isOpen, setIsOpen] = useState(false)
	const ref = useClickOutside<HTMLButtonElement>(() => setIsOpen(false))

	return (
		<Box pos="relative">
			<Button onClick={() => setIsOpen(!isOpen)} size="sm" colorScheme="gray" ref={ref} _focus={{ shadow: "none" }}>
				{"Thêm nhân viên"}
			</Button>
			<Box pos="absolute" right={0} top={0} zIndex={"dropdown"}>
				<Collapse in={isOpen}>
					<VStack
						w="15rem"
						py={2}
						px={2}
						border="1px"
						borderColor={"border.primary"}
						backgroundColor={"background.secondary"}
						align="stretch"
						rounded="md"
					>
						<Text
							w="full"
							px={2}
							py={1}
							_hover={{ bg: "background.third" }}
							rounded="md"
							cursor={"pointer"}
							onClick={() => setIsCreatingEmployee("create")}
						>
							{"Tạo mới"}
						</Text>
						<Text
							w="full"
							px={2}
							py={1}
							_hover={{ bg: "background.third" }}
							rounded="md"
							cursor={"pointer"}
							onClick={() => setIsCreatingEmployee("transfer")}
						>
							{"Chuyển từ chi nhánh khác"}
						</Text>
					</VStack>
				</Collapse>
			</Box>
		</Box>
	)
}

export default AddEmployeeButton
