import { useEffect, useState } from "react"

export const useThrottle = <T>(value: T, timeOut = 200) => {
	const [state, setState] = useState<T>(value)
	const [lastValue, setLastValue] = useState<T>(value)
	const [delay, setDelay] = useState(false)

	useEffect(() => {
		setLastValue(value)
	}, [value])

	useEffect(() => {
		if (!delay) {
			setState(lastValue)
			setDelay(true)
		}
	}, [delay, lastValue])

	useEffect(() => {
		let timeout: NodeJS.Timeout
		if (delay) {
			timeout = setTimeout(() => {
				setDelay(false)
			}, timeOut)
		}
		return () => {
			clearTimeout(timeout)
		}
	}, [delay])

	return state
}

export default useThrottle
