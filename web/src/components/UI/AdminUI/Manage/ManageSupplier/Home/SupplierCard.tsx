import { Supplier } from "@api"
import { AccordionButton, AccordionIcon, AccordionItem, AccordionPanel, Avatar, Box, Button, Flex, HStack, Text } from "@chakra-ui/react"
import Link from "next/link"
import { employeeRoles } from "@constants"
import { baseURL } from "src/api/fetcher"

interface SupplierCardProps {
	data: Supplier
}

const SupplierCard = ({ data }: SupplierCardProps) => {
	return (
		// <Link href={`/admin/manage/supplier/${data.id}`}>
		// 	<Flex key={data.id} bg="white" rounded="md" shadow="base" p={2} align="center" w="full" cursor="pointer">
		// 		{/* <Avatar size="xs" src={`${baseURL}/employee/avatar/${data.id}`} alt={data.name} mr={2} /> */}
		// 		<Text fontWeight={500} w="15rem" isTruncated mr={2} flexShrink={0}>
		// 			{data.name}
		// 		</Text>
		// 		<Text fontSize={"sm"} color="blackAlpha.600" w="15rem" isTruncated flexShrink={0} mr={2}>
		// 			{data.email}
		// 		</Text>

		// 		<Text fontSize={"sm"} color="blackAlpha.600" w="15rem" isTruncated flexShrink={0}>
		// 			{data.address}
		// 		</Text>
		// 		<Text fontSize={"sm"} color="blackAlpha.600" w="8rem" isTruncated flexShrink={0} >
		// 			{data.phone}
		// 		</Text>
		// 	</Flex>
		// </Link>
		<AccordionItem pt={2} border="none">
			<Flex bg="white" rounded="md" shadow="base" align="center" w="full" cursor="pointer">
				<AccordionButton justifyContent="space-between" rounded="md" _expanded={{ bg: '#e1deff' }}>
					<Text fontWeight={500} w="15rem" isTruncated mr={2} flexShrink={0} textAlign="left">
					{data.name}
					</Text>
					<Text fontSize={"sm"} color="blackAlpha.600" w="10rem" isTruncated flexShrink={0}>
					{data.email}
					</Text>
					<Text fontSize={"sm"} color="blackAlpha.600" w="12rem" isTruncated flexShrink={0} >
					{data.address}
					</Text>
					<Text fontSize={"sm"} color="blackAlpha.600" w="8rem" isTruncated flexShrink={0} >
					{data.phone}
					</Text>
					<AccordionIcon justifyContent="right"/>
				</AccordionButton></Flex>
			<AccordionPanel pb={2}>
				<Link href={`/admin/manage/supplier/${data.id}`}>
					<Button size="sm" colorScheme='blue'>
						{"Chi tiết"}
					</Button>
				</Link>
			</AccordionPanel>
		</AccordionItem>
	)
}

export default SupplierCard
