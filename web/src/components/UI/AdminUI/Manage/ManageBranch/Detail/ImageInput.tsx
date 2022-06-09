import { BsCamera } from "react-icons/bs"
import { ChangeEvent } from "react"
import { Flex, Input, Box, Img } from "@chakra-ui/react"
interface Props {
	file: File | string
	onSubmit?: (f: File | null) => void
	readOnly?: boolean
}
export const ImageInput = ({ onSubmit, file, readOnly = false }: Props) => {
	const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
		if (e.target.files && onSubmit) onSubmit(e.target.files[0])
	}

	const getFilePath = () => {
		if (typeof file === "string") return file
		if (file) return URL.createObjectURL(file)
		return ""
	}

	return (
		<Box mb={4}>
			<Box>
				<Flex
					h="10rem"
					border="1px"
					borderColor={"blackAlpha.200"}
					justify="center"
					rounded="md"
					pos="relative"
					backgroundColor={"white"}
				>
					<Img src={getFilePath()} alt="store" />
					{!readOnly && (
						<Box
							pos="absolute"
							top="1rem"
							right="1rem"
							cursor="pointer"
							p={2}
							rounded={"full"}
							border="1px"
							borderColor={"blackAlpha.200"}
							background="gray.900"
							color="white"
						>
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
							<BsCamera />
						</Box>
					)}
				</Flex>
			</Box>
		</Box>
	)
}

export default ImageInput
