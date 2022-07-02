import { useRouter } from "next/router"
import { useMutation, useQuery } from "react-query"
import { client } from "@api"
import { CommonLayout } from ".."
import { adminNavMenus } from "@constants"
interface AdminLayoutProps {
  children: React.ReactNode
}

export const AdminLayout = ({ children }: AdminLayoutProps) => {
  const router = useRouter()

  const selectedMenu = router.pathname.split("/")[2] || ""

  const { data } = useQuery("store-info", () => client.store.getStore())

  const { mutate } = useMutation(() => client.store.logoutStore(), {
    onSuccess: () => {
      router.push("/login")
    }
  })

  return (
    <CommonLayout
      title="BKRM ADMIN"
      menus={adminNavMenus}
      subNavmenus={adminNavMenus.find(m => m.id === selectedMenu)?.subMenus ?? []}
      name={data?.name ?? ""}
      onLogout={mutate}
    >
      {children}
    </CommonLayout>
  )
}

export default AdminLayout
