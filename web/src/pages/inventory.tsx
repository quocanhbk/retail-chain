import InventoryUI from "@components/UI/InventoryUI"
import { AdminLayout } from "@components/UI/Layout"
import { ReactElement } from "react"
import { NextPageWithLayout } from "./_app"

const Inventory: NextPageWithLayout = () => {
	return <InventoryUI />
}

Inventory.getLayout = function getLayout(page: ReactElement) {
	return <AdminLayout>{page}</AdminLayout>
}

export default Inventory
