import { Box, Flex, BoxProps, FlexProps } from "@chakra-ui/layout"
import { motion } from "framer-motion"

export interface MotionBoxProps extends Omit<BoxProps, "transition"> {}
export interface MotionFlexProps extends Omit<FlexProps, "transition"> {}

const MotionBox = motion<MotionBoxProps>(Box)
const MotionFlex = motion<MotionFlexProps>(Flex)

export const Motion = {
	Box: MotionBox,
	Flex: MotionFlex,
}
