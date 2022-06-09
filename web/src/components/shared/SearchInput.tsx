import { InputGroup, InputLeftElement, Input, InputRightElement, IconButton, InputProps } from "@chakra-ui/react"
import { BsSearch, BsX } from "react-icons/bs"

interface SearchInputProps extends InputProps {
	onClear?: () => void
	value: string
	onChange: (e: React.ChangeEvent<HTMLInputElement>) => void
}

export const SearchInput = ({ onClear, value, ...props }: SearchInputProps) => {
	return (
		<InputGroup>
			<InputLeftElement>
				<BsSearch />
			</InputLeftElement>
			<Input w="full" value={value} background={"background.secondary"} {...props} />
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
