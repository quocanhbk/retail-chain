import { Flex, FlexProps, Grid, Heading, Spinner, Text } from "@chakra-ui/react"
import { AnimatePresence, CustomDomComponent, motion } from "framer-motion"
import { ComponentProps, Fragment } from "react"
import { Motion } from "./Motion"

export const MotionFlex = motion<Omit<FlexProps, "transition">>(Flex)

interface LoadableContainerProps extends ComponentProps<CustomDomComponent<Omit<FlexProps, "transition">>> {
  isLoading: boolean
  children: React.ReactNode
}

export const LoadableContainer = ({ isLoading = false, children }: LoadableContainerProps) => {
  return (
    <AnimatePresence initial={false} exitBeforeEnter>
      {isLoading ? (
        <Motion.Box
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.5 }}
          h="100vh"
          w="full"
          pos="fixed"
          zIndex={"overlay"}
          bg={"fill.primary"}
        >
          <Grid w="full" h="full" placeItems={"center"} pb={24}>
            <Flex
              direction="column"
              align="center"
              backgroundColor={"background.secondary"}
              px={8}
              py={4}
              rounded="lg"
              boxShadow={"lg"}
            >
              <Heading
                fontSize="4xl"
                color="telegram.600"
                // color="white"
                rounded="md"
                px={2}
                py={1}
                fontWeight={"900"}
                fontFamily={"Brandon"}
                mb={4}
              >
                BK RETAIL MANAGEMENT
              </Heading>
              <Spinner color="telegram.500" size="sm" thickness="3px" />
            </Flex>
          </Grid>
        </Motion.Box>
      ) : (
        <Fragment key={"children"}>{children}</Fragment>
      )}
    </AnimatePresence>
  )
}
