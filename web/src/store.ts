import { createStore, action, Action, createTypedHooks } from "easy-peasy"
import { Store, Employee } from "@api"
interface StoreModel {
	storeInfo: Store | null
	setStoreInfo: Action<StoreModel, Store | null>
	employeeInfo: Employee | null
	setEmployeeInfo: Action<StoreModel, Employee | null>
}

const store = createStore<StoreModel>({
	storeInfo: null,
	setStoreInfo: action((state, payload) => {
		state.storeInfo = payload
	}),
	employeeInfo: null,
	setEmployeeInfo: action((state, payload) => {
		state.employeeInfo = payload
	})
})

const typedHooks = createTypedHooks<StoreModel>()
export const useStoreActions = typedHooks.useStoreActions
export const useStoreState = typedHooks.useStoreState

export default store
