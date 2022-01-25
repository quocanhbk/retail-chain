import { Box, Flex, HStack, Text } from "@chakra-ui/react"
import Link from "next/link"
import { useRouter } from "next/router"

interface SubNavMenusProps {
	menu: { id: string; text: string; path: string }[]
}

const SubNavMenus = ({ menu }: SubNavMenusProps) => {
	const router = useRouter()

	const currentIndex = menu.findIndex(item => router.pathname.indexOf(item.path) > -1)

	if (menu.length === 0) return null

	return (
		<Flex w="full" justify="center" background={"telegram.600"}>
			<HStack justify={"center"} spacing={0} pos="relative">
				{menu.map(({ id, text, path }) => (
					<Link href={path} key={id}>
						<Text cursor={"pointer"} color="white" fontWeight={500} w="8rem" textAlign={"center"} p={2}>
							{text}
						</Text>
					</Link>
				))}
				<Box
					pos="absolute"
					w="8rem"
					h="5px"
					bg="telegram.300"
					bottom={0}
					left={currentIndex * 8 + "rem"}
					transition="all 0.25s ease-in-out"
				/>
			</HStack>
		</Flex>
	)
}

export default SubNavMenus
