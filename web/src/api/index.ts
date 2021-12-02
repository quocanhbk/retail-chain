import { addCategoryAPI, getCategoryAPI, updateCategoryAPI, deleteCategoryAPI } from "./inventory/index"

export * from "./authenticate"
export * from "./inventory"

class DataFetcher {
	storeId: number
	branchId: number
	constructor() {
		this.storeId = 0
		this.branchId = 0
	}

	init(storeId: number, branchId: number) {
		this.storeId = storeId
		this.branchId = branchId
	}

	async getCategory() {
		return getCategoryAPI(this.storeId, this.branchId)
	}

	async addCategory(name: string) {
		return addCategoryAPI(this.storeId, this.branchId, name)
	}

	async updateCategory(categoryId: number, name: string, ratio: number) {
		return updateCategoryAPI(this.storeId, this.branchId, categoryId, name, ratio)
	}

	async deleteCategory(categoryId: number) {
		return deleteCategoryAPI(this.storeId, this.branchId, categoryId)
	}
}

const dataFetcher = new DataFetcher()

export default dataFetcher
