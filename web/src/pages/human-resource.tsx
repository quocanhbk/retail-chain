import HumanResourceUI from "@components/UI/HumanResourceUI"
import { MainLayout } from "@components/UI/Layout"
import { ReactElement } from "react"
import { NextPageWithLayout } from "./_app"

const HumanResource: NextPageWithLayout = () => {
	return <HumanResourceUI />
}

HumanResource.getLayout = function getLayout(page: ReactElement) {
	return <MainLayout>{page}</MainLayout>
}

export default HumanResource
