import { client } from "@api"
import { useRouter } from "next/router"
import { ReactNode } from "react"
import { useQuery } from "react-query"
import { LoadableContainer } from "./LoadableContainer"

interface LoadingScreenProps {
  children: ReactNode
}

export const LoadingScreen = ({ children }: LoadingScreenProps) => {
  const router = useRouter()

  const { isLoading } = useQuery("guard", () => client.guard.getGuard(), {
    onSuccess: data => {
      if (data.guard === "employee") {
        router.push("/main")
        return
      }
      if (data.guard === "store") {
        router.push("/admin")
        return
      }
      router.push("/login")
    }
  })

  return <LoadableContainer isLoading={isLoading}>{children}</LoadableContainer>
}

export default LoadingScreen
