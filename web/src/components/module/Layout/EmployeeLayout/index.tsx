import { useState } from "react"
import { useRouter } from "next/router"
import { useMutation, useQuery } from "react-query"
import { useStoreActions, useStoreState } from "@store"
import { getInfoEmployee, logoutEmployee } from "@api"
import { CommonLayout } from ".."
import { employeeNavMenus } from "@constants"
import { BoxProps } from "@chakra-ui/react"
interface AdminLayoutProps {
	children: React.ReactNode
	maxW?: BoxProps["maxW"]
}

export const EmployeeLayout = ({ children, maxW = "64rem" }: AdminLayoutProps) => {
	const router = useRouter()
	const [loading, setLoading] = useState(true)
	const setEmployeeInfo = useStoreActions(action => action.setEmployeeInfo)

	const selectedMenu = router.pathname.split("/")[2] || ""

	useQuery("employee-info", () => getInfoEmployee(), {
		enabled: loading,
		onSuccess: data => {
			setEmployeeInfo(data)
			setLoading(false)
		},
		onError: () => {
			router.push("/login")
			setLoading(false)
		},
		retry: false
	})

	const employeeInfo = useStoreState(s => s.employeeInfo)

	const { mutate } = useMutation(() => logoutEmployee(), {
		onSuccess: () => {
			router.push("/login")
		}
	})

	return (
		<CommonLayout
			title="BKRM"
			isLoading={loading}
			menus={employeeNavMenus}
			subNavmenus={employeeNavMenus.find(m => m.id === selectedMenu)?.subMenus ?? []}
			name={employeeInfo?.name || ""}
			onLogout={mutate}
			maxW={maxW}
		>
			{children}
		</CommonLayout>
	)
}

export default EmployeeLayout
