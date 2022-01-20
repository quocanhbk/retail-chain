import fetcher from "./fetcher"

export interface Supplier {
    id: number
    store_id: number
    name: string
    address: string
    email: string
    phone: string | null
}

export type CreateSupplierInput = Omit<Supplier, "id" | "store_id"> 
export const createSupplier = async (input: CreateSupplierInput): Promise<Supplier> => {
    const formData = new FormData()
    formData.append("name",input.name)
    formData.append("address",input.address)
    formData.append("email",input.email)
    formData.append("phone",input.phone || "")


    const {data} = await fetcher.post("/supplier",formData)
    return data
}

export const getSuppliers = async (): Promise<Supplier[]> => {
    const {data } = await fetcher.get("/supplier")
    return data
}
export const getSupplier = async (id: number): Promise<Supplier> => {
	const { data } = await fetcher.get(`/supplier/${id}`)
	return data
}
export const editSupplier = async (supplierId: number, input: CreateSupplierInput): Promise<Supplier> => {
    const formData = new FormData()
    console.log("input",input)
    formData.append("name",input.name)
    formData.append("address",input.address)
    formData.append("email",input.email)
    formData.append("phone",input.phone || "")
    const {data} = await fetcher.patch(`/supplier/${supplierId}`, formData)
    console.log("data",data)
    return data
}

export const deleteSupplier = async (supplierId: number) : Promise<void> => {
    return await fetcher.delete(`/supplier/${supplierId}`)
}