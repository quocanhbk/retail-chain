import { Button, Flex, Menu, MenuButton, MenuItem, MenuList , Text} from "@chakra-ui/react"
import {IoIosArrowDown} from "react-icons/io"
import {FaFileImport} from "react-icons/fa"

const AddNew = () => {
    return (
		<Flex direction={"row"}>
			<Menu>
				<MenuButton as={Button} rightIcon={<IoIosArrowDown />}>
					<Text>{"Thêm mới"}</Text>
				</MenuButton>
				<MenuList>
					<MenuItem>{"Hàng hóa"}</MenuItem>
					{/* <MenuItem>Create a Copy</MenuItem>
				<MenuItem>Mark as Draft</MenuItem>
				<MenuItem>Delete</MenuItem>
				<MenuItem>Attend a Workshop</MenuItem> */}
				</MenuList>
			</Menu>
            <Button ml={3}><FaFileImport/><Text ml={3}>{"Import"}</Text></Button>
		</Flex>
	)
}

export default AddNew