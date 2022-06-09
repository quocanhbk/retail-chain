import { LoginEmployeeOutput, RegisterStoreOutput } from "./api/auth"
import { createStore, action, Action, createTypedHooks } from "easy-peasy"

interface StoreModel {
	storeInfo: RegisterStoreOutput | null
	setStoreInfo: Action<StoreModel, RegisterStoreOutput | null>
	employeeInfo: LoginEmployeeOutput | null
	setEmployeeInfo: Action<StoreModel, LoginEmployeeOutput | null>
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
