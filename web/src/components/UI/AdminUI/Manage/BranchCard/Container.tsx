import { Motion } from "@components/shared"

interface ContainerProps {
	custom: number
	children: React.ReactNode
}

const Container = ({ custom, children }: ContainerProps) => {
	const variants = {
		visible: (i: number) => ({
			opacity: 1,
			transition: {
				delay: i * 0.05,
				duration: 0.35,
				type: "tween",
			},
		}),
		hidden: { opacity: 0 },
	}
	return (
		<Motion.Flex
			direction="column"
			align="center"
			bg="gray.50"
			w="15rem"
			h="15rem"
			rounded="md"
			overflow={"hidden"}
			color="blackAlpha.800"
			cursor={"pointer"}
			variants={variants}
			custom={custom}
			animate="visible"
			initial="hidden"
			shadow="base"
			_hover={{ shadow: "lg" }}
		>
			{children}
		</Motion.Flex>
	)
}

export default Container
