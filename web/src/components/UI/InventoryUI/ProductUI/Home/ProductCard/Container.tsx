import { Motion } from "@components/shared"
import { useTheme } from "@hooks"
import { ComponentProps } from "react"

interface ContainerProps extends ComponentProps<typeof Motion["Flex"]> {
	custom: number
	children: React.ReactNode
}

const Container = ({ custom, children, ...rest }: ContainerProps) => {
	const variants = {
		visible: (i: number) => ({
			opacity: 1,
			transition: {
				delay: i * 0.05,
				duration: 0.35,
				type: "tween"
			}
		}),
		hidden: { opacity: 0 }
	}

	const { backgroundSecondary, textPrimary } = useTheme()

	return (
		<Motion.Flex
			direction="column"
			align="center"
			w="12rem"
			h="16rem"
			rounded="md"
			overflow={"hidden"}
			backgroundColor={backgroundSecondary}
			color={textPrimary}
			cursor={"pointer"}
			variants={variants}
			custom={custom}
			animate="visible"
			initial="hidden"
			shadow="base"
			_hover={{ shadow: "lg" }}
			{...rest}
		>
			{children}
		</Motion.Flex>
	)
}

export default Container
