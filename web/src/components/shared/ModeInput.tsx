import { Flex, Input, IconButton, InputProps, FlexProps, Spinner } from "@chakra-ui/react"
import branch from "@pages/admin/manage/branch"
import { useRef, useState } from "react"
import { BsPen } from "react-icons/bs"
import { FaSave } from "react-icons/fa"

interface ModeInputProps extends InputProps {
	flexProps?: FlexProps
	isLoading?: boolean
	onSave?: () => void
}

export const ModeInput = ({ isLoading, flexProps, onSave, ...rest }: ModeInputProps) => {
	const [readOnly, setReadOnly] = useState(true)

	const inputRef = useRef<HTMLInputElement>(null)

	const handleClick = () => {
		if (readOnly) {
			setReadOnly(false)
			inputRef.current?.focus()
		} else {
			onSave?.()
			setReadOnly(true)
		}
	}

	return (
		<Flex w="full" pos="relative" {...flexProps}>
			<Input value={branch.name} readOnly={readOnly} ref={inputRef} {...rest} />
			<IconButton
				zIndex={"docked"}
				icon={isLoading ? <Spinner size="sm" thickness="3px" /> : readOnly ? <BsPen size="1rem" /> : <FaSave size="1rem" />}
				onClick={handleClick}
				aria-label="edit"
				pos="absolute"
				right={"1rem"}
				rounded="full"
				size="xs"
				top="50%"
				transform="translateY(-50%)"
				variant={"ghost"}
				colorScheme={"gray"}
			/>
		</Flex>
	)
}

export default ModeInput
