import { getBranches } from "@api"
import { Box, Heading, Wrap, WrapItem } from "@chakra-ui/react"
import { useQuery } from "react-query"
import BranchCard from "./BranchCard/BranchCard"
import BranchCardSkeleton from "./BranchCard/BranchCardSkeleton"
import CreateBranchCard from "./BranchCard/CreateBranchCard"

const StoreManageUI = () => {
	const { data, isLoading } = useQuery("branches", getBranches)

	const render = () => {
		if (isLoading) {
			return (
				<Wrap>
					{/* Render 12 BranchCardSkeleton */}
					{[...Array(12)].map((_, index) => (
						<WrapItem key={index}>
							<BranchCardSkeleton />
						</WrapItem>
					))}
				</Wrap>
			)
		}
		if (data) {
			return (
				<Wrap>
					{data.map((branch, index) => (
						<WrapItem key={branch.id}>
							<BranchCard data={branch} index={index} />
						</WrapItem>
					))}
					<WrapItem>
						<CreateBranchCard index={data.length} />
					</WrapItem>
				</Wrap>
			)
		}
	}

	return (
		<Box p={4}>
			<Heading mb={4} fontSize={"2xl"}>
				Quản lý chi nhánh
			</Heading>
			{render()}
		</Box>
	)
}

export default StoreManageUI
