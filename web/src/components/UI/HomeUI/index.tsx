import { logout } from "@api"
import { Flex, Grid, Heading } from "@chakra-ui/layout"
import { Button } from "@chakra-ui/react"
import { chakra } from "@chakra-ui/system"
import useStore from "@store"
import { useRouter } from "next/router"
import { useEffect } from "react"
import { useMutation } from "react-query"

interface indexProps {}

const HomeUI = ({}: indexProps) => {
	const info = useStore((s) => s.info)
	const router = useRouter()
	const initInfo = useStore((s) => s.initInfo)
	const { mutate } = useMutation(logout, {
		onSuccess: () => {
			initInfo()
		},
	})

	return (
		<Grid w="full" h="full" placeItems="center">
			<Flex align="center">
				<Heading>
					Hello <chakra.span color="blue.500">{info?.user_info.username}</chakra.span>
				</Heading>
				<Button variant="outline" ml={4} size="sm" onClick={() => mutate()}>
					Log out
				</Button>
			</Flex>
		</Grid>
	)
}

export default HomeUI
