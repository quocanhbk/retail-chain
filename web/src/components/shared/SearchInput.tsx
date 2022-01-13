import { InputGroup, InputLeftElement, Input, InputRightElement, IconButton, InputGroupProps } from "@chakra-ui/react"
import { BsSearch, BsX } from "react-icons/bs"

interface SearchInputProps extends InputGroupProps {
	onClear?: () => void
	value: string
	onChange: (e: React.ChangeEvent<HTMLInputElement>) => void
}

export const SearchInput = ({ onClear, value, ...props }: SearchInputProps) => {
	return (
		<InputGroup {...props}>
			<InputLeftElement>
				<BsSearch />
			</InputLeftElement>
			<Input w="full" background="white" value={value} {...props} />
			<InputRightElement>
				<IconButton
					variant="ghost"
					aria-label="clear-search"
					icon={<BsX size="1.5rem" />}
					rounded="full"
					size="sm"
					colorScheme={value ? "red" : "gray"}
					onClick={onClear}
					_focus={{ shadow: "none" }}
					disabled={!value}
				/>
			</InputRightElement>
		</InputGroup>
	)
}

export default SearchInput
