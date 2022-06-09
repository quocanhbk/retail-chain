import { Accordion, Box } from "@chakra-ui/react"
import ItemAccordion from "./ItemAccordion"
import THead from "./THead"

const ProductTable = () => {
    return (
        <Box w="full" minW={"60rem"}>
            <THead></THead>
            <Box w="full" h="1px" bg={"gray.300"} mb={"3"}></Box>
            <Accordion allowMultiple>
            <ItemAccordion/>
            <ItemAccordion/>
            <ItemAccordion/>
            </Accordion>
        </Box>
    )
}

export default ProductTable