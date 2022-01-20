import { Avatar, Box, Input } from "@chakra-ui/react"
import { useTheme } from "@hooks"
import { ChangeEvent, useMemo } from "react"
import { BsCamera } from "react-icons/bs"

interface AvatarInputProps {
	file: File | string | null
	onSubmit: (f: File | null) => void
	readOnly?: boolean
}

const AvatarInput = ({ file, onSubmit, readOnly = false }: AvatarInputProps) => {
	const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
		if (e.target.files) onSubmit(e.target.files[0])
	}

	const imagePath = useMemo(() => {
		if (typeof file === "string") return file
		if (file) return URL.createObjectURL(file)
		return ""
	}, [file])

	const { borderPrimary, backgroundSecondary } = useTheme()

	return (
		<Box
			boxSize="8rem"
			rounded="full"
			border="1px"
			borderColor={borderPrimary}
			mb={4}
			backgroundColor={backgroundSecondary}
			pos="relative"
		>
			<Avatar src={imagePath} alt="avatar" boxSize={"8rem"} />
			{!readOnly && (
				<Box
					pos="absolute"
					bottom="0%"
					right="0%"
					cursor="pointer"
					p={2}
					rounded={"full"}
					border="1px"
					borderColor={borderPrimary}
					backgroundColor={backgroundSecondary}
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

export default AvatarInput
