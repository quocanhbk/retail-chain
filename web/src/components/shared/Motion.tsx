import { Box, Flex, BoxProps, FlexProps } from "@chakra-ui/layout"
import { motion } from "framer-motion"

export type MotionBoxProps = Omit<BoxProps, "transition">
export type MotionFlexProps = Omit<FlexProps, "transition">

const MotionBox = motion<MotionBoxProps>(Box)
const MotionFlex = motion<MotionFlexProps>(Flex)

export const Motion = {
	Box: MotionBox,
	Flex: MotionFlex,
}
