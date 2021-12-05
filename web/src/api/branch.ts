import { AuthState, Branch } from "@@types"
import { adminFetcher } from "./fetcher"

export const createBranch = async (
	input: Pick<Branch, "name" | "address">
): Promise<{ state: AuthState; info: { branch_id: number } }> => {
	const { data } = await adminFetcher.post("/branch", input)
	return data
}

export const getBranches = async (): Promise<{ state: AuthState; info: { branches: Branch[] } }> => {
	const { data } = await adminFetcher.get("/branch")
	return data
}
