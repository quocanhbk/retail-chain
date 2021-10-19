import { LoginUI } from "@components/UI"
import HomeUI from "@components/UI/HomeUI"
import useStore from "@store"
import type { NextPage } from "next"

const Home: NextPage = () => {
	const info = useStore((s) => s.info)
	return info?.token ? <HomeUI /> : <LoginUI />
}

export default Home
