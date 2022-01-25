import { Box, Img, Stack, Text } from "@chakra-ui/react"
import { useTheme } from "@hooks"
import QuantityChanger from "./QuantityChanger"
import useCreateImport from "./useCreateImport"

interface PurchaseItemProps {
	data: ReturnType<typeof useCreateImport>["mappedValues"][number]
}

const PurchaseItem = ({ data }: PurchaseItemProps) => {
	const theme = useTheme()

	return (
		<Stack direction="row" h="4rem">
			<Img src={data.item.image} boxSize={"2.5rem"} rounded="md" />
			<Box>
				<Text>{data.item.name}</Text>
				<Text color={theme.textSecondary}>{data.item.barcode}</Text>
			</Box>
			<QuantityChanger value={data.quantity} onChange={data.onChangeQuantity} />
		</Stack>
	)
}

export default PurchaseItem
