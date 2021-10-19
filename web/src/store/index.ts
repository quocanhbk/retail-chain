import { LoginInput, LoginOutput } from "@api"
import create from "zustand"

// luu token trong state la giai phap tam thoi, do chua tim cach de token trong cookie duoc
export interface StoreState {
	info: Pick<LoginOutput, "token" | "user_info"> | null
	initInfo: (loginOutput?: LoginOutput) => void
	authData: LoginInput
	initAuthData: (loginInput: LoginInput) => void
}

const useStore = create<StoreState>((set) => ({
	info: null,
	initInfo: (loginOutput?: LoginOutput) =>
		set(() => {
			if (loginOutput) return { info: { token: loginOutput.token, user_info: loginOutput.user_info } }
			else return { info: null }
		}),
	authData: { username: "", password: "" },
	initAuthData: (loginInput: LoginInput) => set(() => ({ authData: loginInput })),
}))

export default useStore
