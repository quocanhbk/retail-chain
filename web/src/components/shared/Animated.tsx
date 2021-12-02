import { ComponentProps } from "react"
import { Motion } from "."

const variants = {
	slideIn: {
		initial: {
			opacity: 0,
			y: "100%",
		},
		animate: {
			opacity: 1,
			y: 0,
			transition: {
				type: "tween",
				duration: "0.5",
				ease: "easeOut",
			},
		},
	},
} as const

type Variant = keyof typeof variants

interface AnimatedProps extends ComponentProps<typeof Motion.Box> {
	variant: Variant
}

const AnimatedBox = ({ variant, ...rest }: AnimatedProps) => {
	return <Motion.Box variants={variants[variant]} initial="initial" animate="animate" {...rest} />
}

const AnimatedFlex = ({ variant, ...rest }: AnimatedProps) => {
	return <Motion.Flex variants={variants[variant]} initial="initial" animate="animate" {...rest} />
}

export const Animated = {
	Box: AnimatedBox,
	Flex: AnimatedFlex,
}
