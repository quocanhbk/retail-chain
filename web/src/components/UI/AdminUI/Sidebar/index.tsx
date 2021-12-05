import { Box } from "@chakra-ui/layout"
import MenuLink from "./MenuLink"
import {AiOutlineDashboard} from "react-icons/ai"
import {MdStoreMallDirectory,MdPeopleAlt} from "react-icons/md"

interface AdminSidebarProps {}

const Sidebar = ({}: AdminSidebarProps) => {
	return (
		<Box shadow="base" w="16%" p={7}>
			<MenuLink href="/admin/dashboard" text="Tổng quan" icon={AiOutlineDashboard} active={false} />
			<MenuLink href="/admin/branch" text="Chi nhánh" icon={MdStoreMallDirectory} active={true} />
			<MenuLink href="/admin/employee" text="Nhân viên" icon={MdPeopleAlt} active={false} />	
		</Box>
	)
}

export default Sidebar
