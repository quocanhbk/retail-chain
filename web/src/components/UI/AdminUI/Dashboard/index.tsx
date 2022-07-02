import { Grid, Heading, IconButton } from "@chakra-ui/react"
import { BsList } from "react-icons/bs"
import { FaHamburger } from "react-icons/fa"

const StoreDashboardUI = () => {
  return (
    <Grid h="full" placeItems={"center"}>
      <IconButton icon={<BsList size="1.5rem" />} aria-label="Toggle menu" rounded={"full"} colorScheme="telegram" />
      <Heading color={"text.secondary"}>This page is under development</Heading>
    </Grid>
  )
}

export default StoreDashboardUI
