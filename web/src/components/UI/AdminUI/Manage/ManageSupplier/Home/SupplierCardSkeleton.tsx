import { chakra, Skeleton, Text } from "@chakra-ui/react"

const SupplierCardSkeleton = () => {
	return (
		<chakra.tr>
			<chakra.td textAlign={"center"}>
				<Skeleton>
					<Text>CODE</Text>
				</Skeleton>
			</chakra.td>
			<chakra.td textAlign={"center"}>
				<Skeleton>
					<Text>NAME</Text>
				</Skeleton>
			</chakra.td>
			<chakra.td textAlign={"center"}>
				<Skeleton>
					<Text>PHONE</Text>
				</Skeleton>
			</chakra.td>
			<chakra.td textAlign={"center"}>
				<Skeleton>
					<Text>EMAIL</Text>
				</Skeleton>
			</chakra.td>
		</chakra.tr>
	)
}

export default SupplierCardSkeleton
