import { Info } from "@@types"
import create from "zustand"

// luu token trong state la giai phap tam thoi, do chua tim cach de token trong cookie duoc
export interface StoreState {
	info: Info | null
	setInfo: (info: Info) => void
}

const useStore = create<StoreState>(set => ({
	info: null,
	setInfo: (info: Info | null) => {
		set(() => {
			return { info }
		})
	},
}))

export default useStore
