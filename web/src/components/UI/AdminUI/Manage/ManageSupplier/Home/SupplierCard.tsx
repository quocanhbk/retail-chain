import { Supplier } from "@api"
import { chakra, Text } from "@chakra-ui/react"
import { useRouter } from "next/router"
interface SupplierCardProps {
	data: Supplier
}

const SupplierCard = ({ data }: SupplierCardProps) => {
	const router = useRouter()

	return (
		<chakra.tr
			onClick={() => router.push(`/admin/manage/supplier/${data.id}`)}
			cursor="pointer"
			_hover={{ backgroundColor: "background.fade" }}
		>
			<chakra.td textAlign={"center"}>
				<Text>{data.code}</Text>
			</chakra.td>
			<chakra.td textAlign={"center"}>
				<Text>{data.name}</Text>
			</chakra.td>
			<chakra.td textAlign={"center"}>
				<Text>{data.phone}</Text>
			</chakra.td>
			<chakra.td textAlign={"center"}>
				<Text>{data.email}</Text>
			</chakra.td>
		</chakra.tr>
	)
}

export default SupplierCard
