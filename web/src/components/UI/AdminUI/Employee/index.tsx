import { Box } from "@chakra-ui/layout"
import { Button, Table, Thead, Icon, Th, Td, Tr, Tbody, Flex } from "@chakra-ui/react"
import { BsPlusLg } from "react-icons/bs"
import { TextControl } from "@components/shared"

const branchs = [
	{
		name: "Trần Văn Viển",
		phone: "0971845946",
		email: "chinhanh1@gmail.com",
		ngay_tao: "12/12/2021",
		status: true,
	},
	{
		name: "Trần Văn Viển",
		phone: "0971845946",
		email: "chinhanh1@gmail.com",
		ngay_tao: "12/12/2021",
		status: true,
	},
	{
		name: "Trần Văn Viển",
		phone: "0971845946",
		email: "chinhanh1@gmail.com",
		ngay_tao: "12/12/2021",
		status: false,
	},
]
const EmployeeAdminUI = () => {
	return (
		<Box p={7}>
			<Flex align="center">
				<TextControl
					label="Tim kiem"
					display="flex"
					w="300px"
					size="sm"
					mr={3}
					alignItems="center"
					mb={0}
				></TextControl>
				<Button size="sm">
					<Icon as={BsPlusLg} mr={2} />
					Thêm nhân viên
				</Button>
			</Flex>

			<Table size="md" variant="striped" colorScheme="teal" borderWidth="1px" borderRadius="5px" boxShadow="xs">
				<Thead>
					<Tr>
						<Th fontSize="16px">#</Th>
						<Th fontSize="16px">Tên nhân viên</Th>
						<Th fontSize="16px">Số điện thoại</Th>
						<Th fontSize="16px">Email</Th>
						<Th fontSize="16px">Ngày tạo</Th>
						<Th fontSize="16px">Trạng thái</Th>
						<Th isNumeric fontSize="16px">
							Thao tác
						</Th>
					</Tr>
				</Thead>
				<Tbody>
					{branchs.map((item, index) => {
						return (
							<Tr>
								<Td py={2}>{index + 1}</Td>
								<Td py={2}>{item.name}</Td>
								<Td py={2}>{item.phone}</Td>
								<Td py={2}>{item.email}</Td>
								<Td py={2}>{item.ngay_tao}</Td>
								<Td py={2}>{item.status ? "Hoạt động" : "Vô hiệu hóa"}</Td>
								<Td isNumeric py={2}>
									<Button size="sm" mr={2}>
										Chi tiết
									</Button>
									<Button size="sm">Chỉnh sửa</Button>
								</Td>
							</Tr>
						)
					})}
				</Tbody>
			</Table>
		</Box>
	)
}

export default EmployeeAdminUI
