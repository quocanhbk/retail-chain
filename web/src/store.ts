import { RegisterStoreOutput } from "./api/auth"
import { createStore, action, Action, createTypedHooks } from "easy-peasy"

interface StoreModel {
	info: RegisterStoreOutput | null
	setInfo: Action<StoreModel, RegisterStoreOutput | null>
}

const store = createStore<StoreModel>({
	info: null,
	setInfo: action((state, payload) => {
		state.info = payload
	}),
})

const typedHooks = createTypedHooks<StoreModel>()
export const useStoreActions = typedHooks.useStoreActions
export const useStoreState = typedHooks.useStoreState

export default store
