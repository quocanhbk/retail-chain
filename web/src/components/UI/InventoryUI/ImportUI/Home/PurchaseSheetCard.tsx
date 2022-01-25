import { PurchaseSheet, Employee, Supplier } from "@api"
import { Box, theme, Flex, Text } from "@chakra-ui/react"
import { useTheme } from "@hooks"
import { format } from "date-fns"
import { BsTruck, BsPerson, BsClock, BsCurrencyDollar } from "react-icons/bs"

interface PurchaseSheetCardProps {
	data: PurchaseSheet & {
		employee: Employee
		supplier: Supplier
		created_at: string
	}
}

const PurchaseSheetCard = ({ data: ps }: PurchaseSheetCardProps) => {
	const theme = useTheme()

	return (
		<Box
			key={ps.id}
			py={1}
			align="stretch"
			background={theme.backgroundSecondary}
			rounded="md"
			w="15rem"
			cursor="pointer"
			_hover={{ bg: theme.backgroundThird }}
		>
			<Box borderBottom={"1px"} borderColor={theme.borderPrimary} py={2} px={4}>
				<Text fontWeight={"bold"} w="full">
					{ps.code}
				</Text>
			</Box>
			<Box py={2} px={4}>
				<Flex align="center" mb={2}>
					<Box mr={4}>
						<BsTruck size="1rem" />
					</Box>
					<Text>{ps.supplier.name}</Text>
				</Flex>
				<Flex align="center" mb={2}>
					<Box mr={4}>
						<BsPerson size="1rem" />
					</Box>
					<Text>{ps.employee.name}</Text>
				</Flex>
				<Flex align="center" mb={2}>
					<Box mr={4}>
						<BsClock size="1rem" />
					</Box>
					<Text>{format(new Date(ps.created_at), "HH:mm dd/MM/yyyy")}</Text>
				</Flex>
				<Flex align="center" mb={2} color={ps.total - ps.paid_amount > 0 ? theme.fillDanger : theme.textSecondary}>
					<Box mr={4}>
						<BsCurrencyDollar size="1rem" />
					</Box>
					<Text fontWeight={ps.total - ps.paid_amount > 0 ? "bold" : "normal"}>Tiền cần trả: {ps.total - ps.paid_amount}</Text>
				</Flex>
			</Box>
		</Box>
	)
}

export default PurchaseSheetCard
