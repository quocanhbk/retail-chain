import InventoryUI from "@components/UI/InventoryUI"
import { MainLayout } from "@components/UI/Layout"
import { ReactElement } from "react"
import { NextPageWithLayout } from "./_app"

const Inventory: NextPageWithLayout = () => {
	return <InventoryUI />
}

Inventory.getLayout = function getLayout(page: ReactElement) {
	return <MainLayout>{page}</MainLayout>
}

export default Inventory
