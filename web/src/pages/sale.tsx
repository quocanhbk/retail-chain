import { MainLayout } from "@components/UI/Layout"
import SaleUI from "@components/UI/SaleUI"
import { ReactElement } from "react"
import { NextPageWithLayout } from "./_app"

const Sale: NextPageWithLayout = () => {
	return <SaleUI />
}

Sale.getLayout = function getLayout(page: ReactElement) {
	return <MainLayout>{page}</MainLayout>
}

export default Sale
