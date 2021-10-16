import { BsTrash } from "react-icons/bs"
import { ChangeEvent, FC } from "react"
import { Flex, Button, Input, Box } from "@chakra-ui/react"
interface Props {
	file: File | null
	onSubmit: (f: File | null) => void
}
const UploadInput: FC<Props> = ({ onSubmit, file }) => {
	const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
		if (e.target.files) onSubmit(e.target.files[0])
	}

	return (
		<Box
			pos="relative"
			cursor="pointer"
			rounded="md"
			height="2.5rem"
			border="1px"
			borderColor="whiteAlpha.300"
			px={2}
		>
			{file ? (
				<Flex
					align="center"
					justify="space-between"
					px={2}
					height="100%"
					width="100%"
				>
					<p>{file.name}</p>
					<Button
						size="sm"
						color="red"
						onClick={() => onSubmit(null)}
						p={0}
						variant="unstyled"
					>
						<BsTrash size="1.2rem" />
					</Button>
				</Flex>
			) : (
				<Flex align="center" justify="center" px={2} height="100%">
					Upload
					<Input
						pos="absolute"
						type="file"
						top="0"
						left="0"
						width="100%"
						height="100%"
						zIndex="50"
						cursor="pointer"
						onChange={handleChange}
						title=""
						accept="image/png, image/jpeg"
						opacity="0"
					/>
				</Flex>
			)}
		</Box>
	)
}

export default UploadInput
