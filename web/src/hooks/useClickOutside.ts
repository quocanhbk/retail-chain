import { useOutsideClick } from "@chakra-ui/react"
import { useRef } from "react"

export const useClickOutside = <E extends HTMLElement>(handler: () => void, enabled = true) => {
	const ref = useRef<E>(null)
	useOutsideClick({
		ref,
		handler,
		enabled
	})

	return ref
}
