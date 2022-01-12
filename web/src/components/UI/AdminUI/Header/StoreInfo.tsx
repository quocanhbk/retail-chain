import { logoutStore } from "@api"
import { Box, Collapse, Flex, Text, useOutsideClick } from "@chakra-ui/react"
import { useStoreState } from "@store"
import { useRouter } from "next/router"
import { useRef, useState } from "react"
import { BsPower, BsThreeDots } from "react-icons/bs"
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
						<Box background="white" shadow="base" rounded="md" w="10rem" p={2}>
							<Flex
								align="center"
								w="full"
								cursor="pointer"
								onClick={() => mutateLogoutStore()}
								px={2}
								py={1}
								color="red.600"
							>
								<BsPower />
								<Text onClick={() => mutateLogoutStore()} ml={2}>
									Đăng xuất
								</Text>
							</Flex>
						</Box>
					</Collapse>
				</Box>
			</Box>
		</Flex>
	)
}

export default StoreInfo
