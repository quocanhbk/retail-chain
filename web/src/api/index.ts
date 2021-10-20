import { addCategoryAPI, getCategoryAPI, updateCategoryAPI, deleteCategoryAPI } from "./inventory/index"

export * from "./authenticate"
export * from "./inventory"

class DataFetcher {
	storeId: number
	branchId: number
	token: string
	constructor() {
		this.storeId = 0
		this.branchId = 0
		this.token = ""
	}
	init(storeId: number, branchId: number, token: string) {
		this.storeId = storeId
		this.branchId = branchId
		this.token = token
	}

	async getCategory() {
		return getCategoryAPI(this.storeId, this.branchId, this.token)
	}

	async addCategory(name: string) {
		return addCategoryAPI(this.storeId, this.branchId, this.token, name)
	}

	async updateCategory(categoryId: number, name: string, ratio: number) {
		return updateCategoryAPI(this.storeId, this.branchId, this.token, categoryId, name, ratio)
	}

	async deleteCategory(categoryId: number) {
		return deleteCategoryAPI(this.storeId, this.branchId, this.token, categoryId)
	}
}

const dataFetcher = new DataFetcher()

export default dataFetcher
