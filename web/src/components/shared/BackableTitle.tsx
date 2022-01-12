import { Box, Flex, FlexProps, Heading } from "@chakra-ui/react"
import { ReactNode } from "react"
import { BsArrowLeftShort } from "react-icons/bs"
import Link from "next/link"

interface BackableTitleProps extends FlexProps {
	backPath: string
	text: ReactNode
}

export const BackableTitle = ({ backPath, text, ...rest }: BackableTitleProps) => {
	return (
		<Flex align="center" mb={4} {...rest}>
			<Link href={backPath}>
				<Box cursor="pointer" mr={2}>
					<BsArrowLeftShort size="2rem" />
				</Box>
			</Link>
			<Heading fontSize={"2xl"}>{text}</Heading>
		</Flex>
	)
}

export default BackableTitle
