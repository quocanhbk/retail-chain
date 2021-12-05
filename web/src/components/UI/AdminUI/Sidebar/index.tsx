import { Box } from "@chakra-ui/layout"
import MenuLink from "./MenuLink"

interface AdminSidebarProps {}

const Sidebar = ({}: AdminSidebarProps) => {
	return (
		<Box shadow="base">
			<MenuLink href="/admin/branch" text="Chi nhánh" />
			<MenuLink href="/admin/report" text="Báo cáo" />
		</Box>
	)
}

export default Sidebar
