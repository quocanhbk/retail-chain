import { Grid, Spinner } from "@chakra-ui/react"
import { AnimatePresence } from "framer-motion"
import { ComponentProps } from "react"
import { Motion } from "."

interface LoadingOverlayProps extends ComponentProps<typeof Motion["Box"]> {
	isLoading: boolean
}

export const LoadingOverlay = ({ isLoading, ...rest }: LoadingOverlayProps) => {
	return (
		<AnimatePresence exitBeforeEnter initial={false}>
			{isLoading && (
				<Motion.Box
					initial={{ opacity: 0 }}
					animate={{ opacity: 1 }}
					exit={{ opacity: 0 }}
					transition={{ duration: 0.5 }}
					h="full"
					w="full"
					pos="absolute"
					zIndex={"overlay"}
					{...rest}
				>
					<Grid placeItems={"center"} pos="absolute" top={0} left={0} w="full" h="full" zIndex={"modal"}>
						<Spinner size="xl" thickness="3px" />
					</Grid>
				</Motion.Box>
			)}
		</AnimatePresence>
	)
}

export default LoadingOverlay
