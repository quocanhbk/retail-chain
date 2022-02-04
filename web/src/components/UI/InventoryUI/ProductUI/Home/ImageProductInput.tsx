import { Box, Image, Input } from "@chakra-ui/react"
import { ChangeEvent, useMemo } from "react"
import { BsCamera } from "react-icons/bs"

interface ImageProductInputProps {
	file: File | string | null
	onSubmit: (f: File | null) => void
	readOnly?: boolean
}

const ImageProductInput = ({ file, onSubmit, readOnly = false }: ImageProductInputProps) => {
	const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
		if (e.target.files) onSubmit(e.target.files[0])
	}

	const imagePath = useMemo(() => {
		if (typeof file === "string") return file
		if (file) return URL.createObjectURL(file)
		return ""
	}, [file])

	return (
		<Box w="8rem" h="7rem" border="1px" borderColor={"border.primary"} mb={4} backgroundColor={"background.secondary"} pos="relative">
			<Image src={imagePath} alt="image" w="8rem" h="7rem" fallbackSrc="https://via.placeholder.com/120x100" />
			{!readOnly && (
				<Box
					pos="absolute"
					bottom="0%"
					right="0%"
					cursor="pointer"
					p={2}
					border="1px"
					borderColor={"border.primary"}
					backgroundColor={"background.secondary"}
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
		</Box>
	)
}

export default ImageProductInput
