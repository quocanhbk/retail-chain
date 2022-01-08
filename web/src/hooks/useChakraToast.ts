import { useCallback } from "react"
import { useToast, UseToastOptions } from "@chakra-ui/react"

type UseChakraToastOptions = {
	defaultDuration: number
}

type ChakraToastOptions = {
	status: UseToastOptions["status"]
	title: string
	message?: string
	duration?: number
}

export const useChakraToast = ({ defaultDuration }: UseChakraToastOptions = { defaultDuration: 2500 }) => {
	const toast = useToast()

	return useCallback(
		(options: ChakraToastOptions) => {
			const { status, title, message, duration = defaultDuration } = options
			setTimeout(
				() =>
					toast({
						position: "bottom",
						variant: "subtle",
						title,
						description: message,
						status,
						duration,
					}),
				250
			)
		},
		[toast, defaultDuration]
	)
}

export default useChakraToast
