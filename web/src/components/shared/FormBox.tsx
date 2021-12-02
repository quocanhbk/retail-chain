import { chakra, Heading } from "@chakra-ui/react"
import React, { ComponentProps, ReactNode } from "react"
import { Animated } from "."

interface FormBoxProps extends Omit<ComponentProps<typeof Animated.Box>, "variant"> {
	heading: ReactNode
	children: ReactNode
}

export const FormBox = ({ heading, children, ...rest }: FormBoxProps) => {
	return (
		<Animated.Flex
			flexDirection="column"
			variant="slideIn"
			maxW="full"
			w="20rem"
			p={4}
			rounded="md"
			shadow="base"
			bg="white"
			{...rest}
		>
			<Heading mb={4}>{heading}</Heading>
			<chakra.form mb={4} onSubmit={e => e.preventDefault()} flex={1}>
				{children}
			</chakra.form>
		</Animated.Flex>
	)
}

export default FormBox
