import { Info } from "@@types"
import { createStore, action, Action, createTypedHooks } from "easy-peasy"

interface Store {
	info: Info | null
	setInfo: Action<Store, Info | null>
}

export const store = createStore<Store>({
	info: null,
	setInfo: action((state, payload) => {
		state.info = payload
	}),
})

const typedHooks = createTypedHooks<Store>()

export const useStoreActions = typedHooks.useStoreActions
export const useStoreState = typedHooks.useStoreState
