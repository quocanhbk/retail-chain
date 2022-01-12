import { AuthLayout } from "@components/module"
import { LoginUI } from "@components/UI"
import { ReactElement } from "react"
import { NextPageWithLayout } from "./_app"

const LoginPage: NextPageWithLayout = () => {
	return <LoginUI />
}

LoginPage.getLayout = function getLayout(page: ReactElement) {
	return <AuthLayout>{page}</AuthLayout>
}

export default LoginPage
