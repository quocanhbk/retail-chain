import { PurchaseSheetDetail } from "@api"
import { chakra } from "@chakra-ui/react"
import { format } from "date-fns"
import { currency } from "@helper"
import { useRouter } from "next/router"
interface PurchaseSheetCardProps {
	data: Omit<PurchaseSheetDetail, "purchaseSheetItems" | "branch">
}

const PurchaseSheetCard = ({ data: ps }: PurchaseSheetCardProps) => {
	const router = useRouter()
	return (
		<chakra.tr
			onClick={() => router.push(`/main/inventory/import/${ps.id}`)}
			cursor="pointer"
			_hover={{
				bg: "background.third"
			}}
		>
			<chakra.td p={2}>{ps.code}</chakra.td>
			<chakra.td p={2} textAlign={"center"}>
				{ps.supplier.name}
			</chakra.td>
			<chakra.td p={2} textAlign={"center"}>
				{format(new Date(ps.created_at), "HH:mm dd/MM/yyyy")}
			</chakra.td>
			<chakra.td p={2} textAlign={"right"}>
				{currency(ps.total)}
			</chakra.td>
			<chakra.td
				p={2}
				textAlign={"right"}
				color={ps.total - ps.paid_amount > 0 ? "fill.danger" : "text.primary"}
				fontWeight={ps.total - ps.paid_amount > 0 ? "bold" : "normal"}
			>
				{currency(ps.total - ps.paid_amount)}
			</chakra.td>
		</chakra.tr>
	)
}

export default PurchaseSheetCard
