import { Flex } from "@chakra-ui/react"
import { useRouter } from "next/router"
import Header, { HeaderProps } from "./Header"
import { AnimatePresence } from "framer-motion"
interface CommonLayoutProps extends HeaderProps {
  children: React.ReactNode
}

export const CommonLayout = ({ children, ...headerProps }: CommonLayoutProps) => {
  const router = useRouter()
  return (
    <Flex direction="column" h="100vh" backgroundColor={"background.primary"}>
      <Header {...headerProps} />
      <AnimatePresence exitBeforeEnter initial={false}>
        <Flex
          flex={1}
          w="full"
          justify={"center"}
          overflow={"auto"}
          backgroundColor={"background.primary"}
          key={router.pathname}
        >
          {children}
        </Flex>
      </AnimatePresence>
    </Flex>
  )
}

export default CommonLayout
