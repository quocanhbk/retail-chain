import { UseMutationOptions } from "react-query"

export type MutationOptions<OutputType, InputType> =
	| Omit<UseMutationOptions<OutputType, unknown, InputType, unknown>, "mutationFn">
	| undefined
