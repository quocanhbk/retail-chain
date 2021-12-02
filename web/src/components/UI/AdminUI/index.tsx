import { Box, Text, Button } from "@chakra-ui/react"
import { useLogout } from "@hooks"
import useStore from "@store"

const AdminUI = () => {
	const info = useStore(s => s.info)
	const { mutate, isLoading } = useLogout()
	return (
		<Box>
			<Text>Hello {info?.user.name}</Text>
			<Button onClick={() => mutate()} isLoading={isLoading}>
				Logout
			</Button>
		</Box>
	)
}

export default AdminUI
