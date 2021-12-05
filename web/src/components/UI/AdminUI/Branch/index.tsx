import { Box } from "@chakra-ui/layout"
import { Button, Table, Thead, Icon, Th, Td, Tr, Tbody } from "@chakra-ui/react"
import { BsPlusLg } from "react-icons/bs"

const branchs = [
	{
		name: 'Chi nhánh 1',
		local: 'Tp.HCM',
		email: 'chinhanh1@gmail.com'
	},
	{
		name: 'Chi nhánh 1',
		local: 'Tp.HCM',
		email: 'chinhanh1@gmail.com'
	},
	{
		name: 'Chi nhánh 1',
		local: 'Tp.HCM',
		email: 'chinhanh1@gmail.com'
	}
]
const BranchAdminUI = () => {
	return (
		<Box p={7} >
			<Button mb={2} size="sm"><Icon as={BsPlusLg} mr={2} />Thêm chi nhánh</Button>
			<Table size='md' variant='striped' colorScheme='teal' borderWidth="1px" borderRadius="5px" boxShadow="xs" >
				<Thead >
					<Tr >
						<Th fontSize="16px">#</Th>
						<Th fontSize="16px">Tên chi nhánh</Th>
						<Th fontSize="16px">Địa chỉ</Th>
						<Th fontSize="16px">Email</Th>
						<Th isNumeric fontSize="16px">Thao tác</Th>
					</Tr>
				</Thead>
				<Tbody>
					{
						branchs.map((item, index) => {
							return (
								<Tr>
									<Td py={2}>{index + 1}</Td>
									<Td py={2}>{item.name}</Td>
									<Td py={2}>{item.local}</Td>
									<Td py={2}>{item.email}</Td>
									<Td isNumeric py={2}><Button size="sm" mr={2}>Chi tiết</Button><Button size="sm">Chỉnh sửa</Button></Td>
								</Tr>
							)
						})
					}
				</Tbody>
			</Table>
		</Box>
	)
}

export default BranchAdminUI
