import { chakra, Skeleton } from "@chakra-ui/react"

const PurchaseSheetCardSkeleton = () => {
	return (
		<chakra.tr>
			<chakra.td p={2}>
				<Skeleton>CODE</Skeleton>
			</chakra.td>
			<chakra.td p={2} textAlign={"center"}>
				<Skeleton>Supplier name</Skeleton>
			</chakra.td>
			<chakra.td p={2} textAlign={"center"}>
				<Skeleton>Time</Skeleton>
			</chakra.td>
			<chakra.td p={2} textAlign={"right"}>
				<Skeleton>Total</Skeleton>
			</chakra.td>
			<chakra.td p={2} textAlign={"right"}>
				<Skeleton>Need to pay</Skeleton>
			</chakra.td>
		</chakra.tr>
	)
}

export default PurchaseSheetCardSkeleton
