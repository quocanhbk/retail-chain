import { Supplier } from "@api"
import { Box, Flex, Text } from "@chakra-ui/react"
import { BsPhone } from "react-icons/bs"
import { FiMail } from "react-icons/fi"
import Link from "next/link"
interface SupplierCardProps {
	data: Supplier
}

const SupplierCard = ({ data }: SupplierCardProps) => {
	return (
		<Link href={`/admin/manage/supplier/${data.id}`}>
			<Box rounded="md" backgroundColor={"background.secondary"} cursor="pointer" _hover={{ bg: "background.third" }}>
				<Flex align="center" borderBottom={"1px"} borderColor={"border.primary"} px={4} py={2}>
					<Text fontWeight={"bold"} fontSize={"lg"}>
						{data.name}
					</Text>
				</Flex>
				<Box p={4}>
					<Flex align="center" w="full" mb={2}>
						<Box color="text.secondary">
							<BsPhone />
						</Box>
						<Text ml={2} flex={1} isTruncated>
							{data.phone}
						</Text>
					</Flex>
					<Flex align="center" w="full">
						<Box color="text.secondary">
							<FiMail />
						</Box>
						<Text ml={2} flex={1} isTruncated>
							{data.email || "N/A"}
						</Text>
					</Flex>
				</Box>
			</Box>
		</Link>
	)
}

export default SupplierCard
