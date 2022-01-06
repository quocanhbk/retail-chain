import { BsTrash } from "react-icons/bs"
import { ChangeEvent, FC } from "react"
import { Flex, Button, Input, Box, Text } from "@chakra-ui/react"
interface Props {
	label: string
	file: File | null
	onSubmit: (f: File | null) => void
}
export const UploadInput = ({ label, onSubmit, file }: Props) => {
	const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
		if (e.target.files) onSubmit(e.target.files[0])
	}

	return (
		<Box>
			<Text fontWeight={500} mb={1}>
				{label}
			</Text>
			<Box
				pos="relative"
				cursor="pointer"
				rounded="md"
				height="2.5rem"
				border="1px"
				borderColor="gray.200"
				px={2}
			>
				{file ? (
					<Flex align="center" justify="space-between" px={2} height="100%" width="100%">
						<Text isTruncated>{file.name}</Text>
						<Box as="button" color="red" onClick={() => onSubmit(null)}>
							<BsTrash size="1.2rem" />
						</Box>
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
		</Box>
	)
}

export default UploadInput
