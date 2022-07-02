import { Store, client, LoginStoreInput, Employee, EmployeeLoginInput } from "@api"
import { useStoreActions } from "@store"
import { useRouter } from "next/router"
import { useForm } from "react-hook-form"
import { useMutation, useQueryClient } from "react-query"
import * as Yup from "yup"
import { yupResolver } from "@hookform/resolvers/yup"
import { useEffect, useState } from "react"

const validationSchema = Yup.object().shape({
  email: Yup.string().required("Email bắt buộc").email("Email không hợp lệ"),
  password: Yup.string().required("Mật khẩu bắt buộc").min(6, "Mật khẩu phải có ít nhất 6 ký tự"),
  remember: Yup.boolean()
})

const useLogin = (admin: boolean) => {
  const setStoreInfo = useStoreActions(s => s.setStoreInfo)

  const setEmployeeInfo = useStoreActions(s => s.setEmployeeInfo)

  const [generalError, setGeneralError] = useState("")

  const qc = useQueryClient()

  const {
    register,
    handleSubmit: handleSubmitForm,
    formState: { errors },
    watch
  } = useForm<LoginStoreInput | EmployeeLoginInput>({ resolver: yupResolver(validationSchema) })

  useEffect(() => {
    const sub = watch(() => setGeneralError(""))
    return () => sub.unsubscribe()
  }, [watch])

  const router = useRouter()

  const { mutate: mutateLoginEmployee, isLoading: isLoadingLoginEmployee } = useMutation<
    Employee,
    Error,
    EmployeeLoginInput
  >(input => client.employee.loginEmployee(input), {
    onSuccess: data => {
      setEmployeeInfo(data)
      router.push("/")
    },
    onError: error => {
      setGeneralError(error.message)
    }
  })

  const { mutate: mutateLoginStore, isLoading: isLoadingLoginStore } = useMutation<Store, Error, LoginStoreInput>(
    input => client.store.loginStore(input),
    {
      onSuccess: data => {
        setStoreInfo(data)
        router.push("/admin")
      },
      onError: err => {
        setGeneralError(err.message)
      }
    }
  )

  const isLoading = admin ? isLoadingLoginStore : isLoadingLoginEmployee

  const handleSubmit = handleSubmitForm(values => (admin ? mutateLoginStore(values) : mutateLoginEmployee(values)))

  return {
    isLoading,
    errors,
    register,
    handleSubmit,
    generalError
  }
}
export default useLogin
