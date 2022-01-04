import { logoutStore } from "@api"
import { Box, Collapse, Flex, Text, useOutsideClick } from "@chakra-ui/react"
import { useStoreState } from "@store"
import { useRouter } from "next/router"
import { useRef, useState } from "react"
import { BsThreeDots } from "react-icons/bs"
import { useMutation } from "react-query"

const StoreInfo = () => {
	const router = useRouter()

	const info = useStoreState(s => s.info)
	const [isOpen, setIsOpen] = useState(false)
	const boxRef = useRef<HTMLDivElement>(null)
	useOutsideClick({
		ref: boxRef,
		handler: () => setIsOpen(false),
	})

	const { mutate: mutateLogoutStore } = useMutation(() => logoutStore(), {
		onSuccess: () => router.push("/login"),
	})

	return (
		<Flex align="center" borderLeft={"1px"} borderColor={"gray.300"} pl={4}>
			<Text fontWeight={"bold"} mr={2} fontSize={"lg"}>
				{info?.name}
			</Text>
			<Box
				rounded="full"
				p={2}
				cursor={"pointer"}
				pos="relative"
				onClick={() => setIsOpen(isOpen => !isOpen)}
				ref={boxRef}
			>
				<BsThreeDots size="1.2rem" />
				<Box pos="absolute" top="100%" right={0}>
					<Collapse in={isOpen}>
						<Box background="white" border="1px" borderColor={"gray.300"} rounded="md" w="10rem">
							<Text px={2} py={1}>
								Chỉnh sửa
							</Text>
							<Text px={2} py={1} cursor={"pointer"} onClick={() => mutateLogoutStore()}>
								Đăng xuất
							</Text>
						</Box>
					</Collapse>
				</Box>
			</Box>
		</Flex>
	)
}

export default StoreInfo
