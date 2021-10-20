import { MainLayout } from "@components/UI/Layout"
import SettingUI from "@components/UI/SettingUI"
import { ReactElement } from "react"
import { NextPageWithLayout } from "./_app"

const Setting: NextPageWithLayout = () => {
	return <SettingUI />
}

Setting.getLayout = function getLayout(page: ReactElement) {
	return <MainLayout>{page}</MainLayout>
}

export default Setting
