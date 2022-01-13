import { AuthLayout } from "@components/module"
import { RegisterUI } from "@components/UI"
import { ReactElement } from "react"
import { NextPageWithLayout } from "./_app"

const Register: NextPageWithLayout = () => {
	return <RegisterUI />
}

Register.getLayout = function getLayout(page: ReactElement) {
	return <AuthLayout>{page}</AuthLayout>
}

export default Register
