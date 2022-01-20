import { Box, Collapse, Flex, FlexProps, Heading, IconButton } from "@chakra-ui/react"
import { ReactNode, useState } from "react"
import { BsArrowLeftShort, BsThreeDots } from "react-icons/bs"
import Link from "next/link"
import { useClickOutside, useTheme } from "@hooks"

interface BackableTitleProps extends FlexProps {
	backPath: string
	text: ReactNode
	children?: ReactNode
}

export const BackableTitle = ({ backPath, text, children, ...rest }: BackableTitleProps) => {
	const [isOpen, setIsOpen] = useState(false)

	const { backgroundSecondary, borderPrimary } = useTheme()
	const boxRef = useClickOutside<HTMLDivElement>(() => setIsOpen(false))

	return (
		<Flex align="center" justify="space-between" mb={4} {...rest}>
			<Flex align="center">
				<Link href={backPath}>
					<Box cursor="pointer" mr={2}>
						<BsArrowLeftShort size="2rem" />
					</Box>
				</Link>
				<Heading fontSize={"2xl"}>{text}</Heading>
			</Flex>
			{children && (
				<Box
					pos="relative"
					cursor={"pointer"}
					zIndex={"dropdown"}
					onClick={() => setIsOpen(!isOpen)}
					ref={boxRef}
				>
					<IconButton
						icon={<BsThreeDots size="1.2rem" />}
						variant="ghost"
						rounded="full"
						aria-label="more"
						colorScheme={"gray"}
					/>
					<Box pos="absolute" top={"100%"} right={0} transform={"translateY(0.5rem)"}>
						<Collapse in={isOpen}>
							<Box
								bg={backgroundSecondary}
								border="1px"
								borderColor={borderPrimary}
								p={2}
								rounded="md"
								w="10rem"
							>
								{children}
							</Box>
						</Collapse>
					</Box>
				</Box>
			)}
		</Flex>
	)
}

export default BackableTitle
