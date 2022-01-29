import { Supplier } from "@api"
import { Box, Flex, Text } from "@chakra-ui/react"
import { useTheme } from "@hooks"
import { BsPhone } from "react-icons/bs"
import { FiMail } from "react-icons/fi"
import Link from "next/link"
interface SupplierCardProps {
	data: Supplier
}

const SupplierCard = ({ data }: SupplierCardProps) => {
	const theme = useTheme()

	return (
		<Link href={`/admin/manage/supplier/${data.id}`}>
			<Box rounded="md" backgroundColor={theme.backgroundSecondary} cursor="pointer" _hover={{ bg: theme.backgroundThird }}>
				<Flex align="center" borderBottom={"1px"} borderColor={theme.borderPrimary} px={4} py={2}>
					<Text fontWeight={"bold"} fontSize={"lg"}>
						{data.name}
					</Text>
				</Flex>
				<Box p={4}>
					<Flex align="center" w="full" mb={2}>
						<Box>
							<BsPhone />
						</Box>
						<Text ml={2} flex={1} isTruncated>
							{data.phone}
						</Text>
					</Flex>
					<Flex align="center" w="full">
						<Box>
							<FiMail />
						</Box>
						<Text ml={2} flex={1} isTruncated>
							{data.email}
						</Text>
					</Flex>
				</Box>
			</Box>
		</Link>
	)
}

export default SupplierCard
