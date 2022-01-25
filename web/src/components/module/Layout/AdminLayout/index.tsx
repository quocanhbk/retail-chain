import { useState } from "react"
import { useRouter } from "next/router"
import { useMutation, useQuery } from "react-query"
import { useStoreActions, useStoreState } from "@store"
import { getStoreInfo, logoutStore } from "@api"
import { CommonLayout } from ".."
import { adminNavMenus } from "@constants"
interface AdminLayoutProps {
	children: React.ReactNode
}

export const AdminLayout = ({ children }: AdminLayoutProps) => {
	const router = useRouter()
	const [loading, setLoading] = useState(true)
	const setInfo = useStoreActions(action => action.setStoreInfo)

	const selectedMenu = router.pathname.split("/")[2] || ""

	useQuery("store-info", () => getStoreInfo(), {
		enabled: loading,
		onSuccess: data => {
			setInfo(data)
			setLoading(false)
		},
		onError: () => {
			router.push("/login")
			setLoading(false)
		},
		retry: false
	})

	const storeInfo = useStoreState(s => s.storeInfo)

	const { mutate } = useMutation(() => logoutStore(), {
		onSuccess: () => {
			router.push("/login")
		}
	})

	return (
		<CommonLayout
			title="BKRM ADMIN"
			isLoading={loading}
			menus={adminNavMenus}
			subNavmenus={adminNavMenus.find(m => m.id === selectedMenu)?.subMenus ?? []}
			name={storeInfo?.name || ""}
			onLogout={mutate}
		>
			{children}
		</CommonLayout>
	)
}

export default AdminLayout
