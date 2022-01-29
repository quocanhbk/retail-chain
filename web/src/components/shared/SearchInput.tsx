import { InputGroup, InputLeftElement, Input, InputRightElement, IconButton, InputProps } from "@chakra-ui/react"
import { useTheme } from "@hooks"
import { BsSearch, BsX } from "react-icons/bs"

interface SearchInputProps extends InputProps {
	onClear?: () => void
	value: string
	onChange: (e: React.ChangeEvent<HTMLInputElement>) => void
}

export const SearchInput = ({ onClear, value, ...props }: SearchInputProps) => {
	const { backgroundSecondary } = useTheme()
	return (
		<InputGroup>
			<InputLeftElement>
				<BsSearch />
			</InputLeftElement>
			<Input w="full" value={value} background={backgroundSecondary} {...props} />
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
