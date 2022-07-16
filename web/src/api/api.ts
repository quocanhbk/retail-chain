/* eslint-disable */
/* tslint:disable */
/*
 * ---------------------------------------------------------------
 * ## THIS FILE WAS GENERATED VIA SWAGGER-TYPESCRIPT-API        ##
 * ##                                                           ##
 * ## AUTHOR: acacode                                           ##
 * ## SOURCE: https://github.com/acacode/swagger-typescript-api ##
 * ---------------------------------------------------------------
 */

export type Branch = UpsertTime & { id: number; name: string; address: string }

export type BranchDetail = Branch & { employments: EmploymentDetail[] }

export interface CreateBranchInput {
    name: string
    address: string
    transfered_employees?: TransferredEmployeeInput[]
    new_employees?: NewEmployeeInput[]
}

export interface UpdateBranchInput {
    name?: string
    address?: string

    /** @format binary */
    image?: File
}

export interface TransferredEmployeeInput {
    id: number
    role_ids: number[]
}

export interface NewEmployeeInput {
    name: string
    email: string

    /** @format password */
    password: string

    /** @format password */
    password_confirmation?: string
    role_ids: number[]
    phone?: string
    birthday?: string
    gender?: string
}

export type Category = UpsertTime & { id: number; store_id: number; name: string; description: string | null }

export interface UpsertCategoryInput {
    name?: string
    description?: string
}

export type CreateCategoryInput = UpsertCategoryInput

export type CategoryWithItems = Category & { items?: Item[] }

export type Customer = UpsertTime & { id: number; code: string; name: string; phone: string | null; email: string | null }

export type CreateCustomerInput = UpsertCustomerInput

export interface UpsertCustomerInput {
    name?: string
    phone?: string
    email?: string
}

export interface DefaultItem {
    id: number
    product_name: string
    bar_code: string
    qr_code: string | null
    image_url: string | null
    brand?: string | null
    made_in: string | null
    unit: string | null
    mfg_date: string | null
    exp_date: string | null
    description: string | null
    source_url: string
    date: string
    is_duplicate: number
}

export type DefaultItemWithCategory = DefaultItem & { category?: DefaultCategory }

export interface DefaultCategory {
    id: number
    name: string
}

export type Employee = UpsertTime & {
    id: number
    name: string
    email: string
    store_id: number
    avatar: string | null
    avatar_key: string | null
    phone: string | null
    birthday: string | null
    gender: string | null
    email_verified_at: string | null
}

export type EmployeeWithEmployment = Employee & { employment: EmploymentWithRoles }

export interface CreateEmployeeInput {
    name: string
    email: string

    /** @format password */
    password: string

    /** @format password */
    password_confirmation: string
    branch_id: number
    role_ids: number[]
    phone?: string
    birthday?: string
    gender?: string

    /** @format binary */
    avatar?: File
}

export interface EmployeeAvatar {
    /** @format binary */
    avatar?: File
}

export type CreateSingleEmployeeInput = CreateEmployeeInput & EmployeeAvatar

export interface CreateManyEmployeesInput {
    employees?: CreateSingleEmployeeInput[]
}

export interface UpdateEmployeeInput {
    name?: string
    email?: string
    role_ids?: number[]
    phone?: string
    birthday?: string
    gender?: string

    /** @format binary */
    avatar?: File
}

export interface EmployeeLoginInput {
    email: string

    /** @format password */
    password: string
    remember?: boolean
}

export interface TransferEmployeeInput {
    branch_id: number
    employees: { id: number; role_ids: number[] }[]
}

export type Employment = UpsertTime & { id: number; employee_id: number; branch_id: number; from: string; to: string | null }

export type EmploymentWithRoles = Employment & { roles: EmploymentRole[] }

export interface EmploymentRole {
    id: number
    employment_id: number
    role: string
}

export type EmploymentDetail = Employment & { employee: Employee }

export type Item = UpsertTime & {
    id: number
    store_id: number
    barcode: string
    code: string
    name: string
    image: string | null
    image_key: string | null
    category_id: number | null
}

export type ItemWithCategory = Item & { category: Category | null }

export type ItemWithProperties = ItemWithCategory & { properties: ItemProperty[] }

export type CreateItemInput = UpsertItemInput

export interface UpsertItemInput {
    category_id?: number
    code?: string
    barcode?: string
    name?: string

    /** @format binary */
    image?: File
}

export type ItemPriceHistory = UpsertTime & { id: number; item_id: number; price: number; start_date: string; end_date: string }

export type ItemProperty = UpsertTime & {
    id: number
    quantity: number
    sell_price: number
    base_price: number
    last_purchase_price: number | null
    item_id: number
    branch_id: number
}

export interface UpsertTime {
    created_at: string
    updated_at: string
}

export interface Message {
    message: string
}

export type PurchaseSheet = UpsertTime & {
    id: number
    code: string
    employee_id: number | null
    branch_id: number
    supplier_id: number | null
    discount: number | null
    discount_type: "amount" | "percent" | null
    total: number
    paid_amount: number
    note: string
}

export type PurchaseSheetWithSupplierAndEmployee = PurchaseSheet & { supplier: Supplier | null; employee: Employee | null }

export type PurchaseSheetDetail = PurchaseSheetWithSupplierAndEmployee & { branch: Branch; items: PurchaseSheetItemDetail[] }

export interface CreatePurchaseSheetInput {
    code?: string
    supplier_id?: number
    discount?: number
    discount_type?: "percent" | "amount"
    paid_amount?: number
    note?: string
    items: PurchaseSheetItemInput[]
}

export interface PurchaseSheetItemInput {
    id: number
    quantity: number
    price: number
    discount?: number
    discount_type?: "percent" | "amount"
}

export type PurchaseSheetItem = UpsertTime & {
    id: number
    item_id: number
    purchase_sheet_id: number
    quantity: number
    price: number
    discount: number | null
    discount_type: "percent" | "amount" | null
    total: number
}

export type PurchaseSheetItemDetail = PurchaseSheetItem & { item: Item }

export type QuantityCheckingSheet = UpsertTime & { id: number; code: string; employee_id: number; branch_id: number; note: string }

export type QuantityCheckingSheetWithEmployee = QuantityCheckingSheet & { employee: Employee }

export type QuantityCheckingSheetDetail = QuantityCheckingSheetWithEmployee & { items: QuantityCheckingItem[] }

export interface CreateQuantityCheckingSheetInput {
    code?: string
    note?: string
    items: { id: number; actual_quantity: number }[]
}

export type QuantityCheckingItem = UpsertTime & {
    id: number
    quantity_checking_sheet_id: number
    item_id: number
    expected_quantity: number
    actual_quantity: number
    total: number
}

export type Role = UpsertTime & { id: number; store_id: number; name: string; description: string }

export interface UpsertRoleInput {
    name?: string
    description?: string
}

export type CreateRoleInput = UpsertRoleInput

export type Shift = UpsertTime & { id: number; branch_id: number; name: string; start_time: string; end_time: string }

export interface UpsertShiftInput {
    name?: string
    start_time?: string
    end_time?: string
}

export type CreateShiftInput = UpsertShiftInput

export type ShiftWithWorkSchedules = Shift & { workSchedules: WorkScheduleWithEmployee[] }

export type Store = UpsertTime & { id: number; name: string; email: string; email_verified_at: string | null }

export type RegisterStoreInput = LoginStoreInput & { name: string; password_confirmation: string }

export interface LoginStoreInput {
    email: string

    /** @format password */
    password: string
    remember?: boolean
}

export type Supplier = UpsertTime & {
    id: number
    store_id: number
    name: string
    code: string
    address: string | null
    phone: string | null
    email: string | null
    tax_number: string | null
    note: string | null
}

export type CreateSupplierInput = UpdateSupplierInput

export interface UpdateSupplierInput {
    name?: string
    address?: string
    code?: string
    phone?: string
    email?: string
    tax_number?: string
    note?: string
}

export type WorkSchedule = UpsertTime & {
    id: number
    shift_id: number
    employee_id: number
    date: string
    note: string
    is_absent: boolean | null
}

export type CreateWorkScheduleInput = UpdateWorkScheduleInput & { shift_id: number; employee_ids: number[]; date: string }

export interface UpdateWorkScheduleInput {
    note?: string
    is_absent?: boolean
}

export type WorkScheduleWithEmployee = WorkSchedule & { employee: Employee }

export type WorkScheduleWithShiftAndEmployee = WorkScheduleWithEmployee & { shift: Shift }

import axios, { AxiosInstance, AxiosRequestConfig, ResponseType } from "axios"

export type QueryParamsType = Record<string | number, any>

export interface FullRequestParams extends Omit<AxiosRequestConfig, "data" | "params" | "url" | "responseType"> {
    /** set parameter to `true` for call `securityWorker` for this request */
    secure?: boolean
    /** request path */
    path: string
    /** content type of request body */
    type?: ContentType
    /** query params */
    query?: QueryParamsType
    /** format of response (i.e. response.json() -> format: "json") */
    format?: ResponseType
    /** request body */
    body?: unknown
}

export type RequestParams = Omit<FullRequestParams, "body" | "method" | "query" | "path">

export interface ApiConfig<SecurityDataType = unknown> extends Omit<AxiosRequestConfig, "data" | "cancelToken"> {
    securityWorker?: (securityData: SecurityDataType | null) => Promise<AxiosRequestConfig | void> | AxiosRequestConfig | void
    secure?: boolean
    format?: ResponseType
}

export enum ContentType {
    Json = "application/json",
    FormData = "multipart/form-data",
    UrlEncoded = "application/x-www-form-urlencoded"
}

export class HttpClient<SecurityDataType = unknown> {
    public instance: AxiosInstance
    private securityData: SecurityDataType | null = null
    private securityWorker?: ApiConfig<SecurityDataType>["securityWorker"]
    private secure?: boolean
    private format?: ResponseType

    constructor({ securityWorker, secure, format, ...axiosConfig }: ApiConfig<SecurityDataType> = {}) {
        this.instance = axios.create({ ...axiosConfig, baseURL: axiosConfig.baseURL || "http://localhost:8000/api" })
        this.secure = secure
        this.format = format
        this.securityWorker = securityWorker
    }

    public setSecurityData = (data: SecurityDataType | null) => {
        this.securityData = data
    }

    private mergeRequestParams(params1: AxiosRequestConfig, params2?: AxiosRequestConfig): AxiosRequestConfig {
        return {
            ...this.instance.defaults,
            ...params1,
            ...(params2 || {}),
            headers: {
                ...(this.instance.defaults.headers || {}),
                ...(params1.headers || {}),
                ...((params2 && params2.headers) || {})
            }
        }
    }

    private createFormData(input: Record<string, unknown>): FormData {
        return Object.keys(input || {}).reduce((formData, key) => {
            const property = input[key]
            formData.append(
                key,
                property instanceof Blob
                    ? property
                    : typeof property === "object" && property !== null
                    ? JSON.stringify(property)
                    : `${property}`
            )
            return formData
        }, new FormData())
    }

    public request = async <T = any, _E = any>({ secure, path, type, query, format, body, ...params }: FullRequestParams): Promise<T> => {
        const secureParams =
            ((typeof secure === "boolean" ? secure : this.secure) &&
                this.securityWorker &&
                (await this.securityWorker(this.securityData))) ||
            {}
        const requestParams = this.mergeRequestParams(params, secureParams)
        const responseFormat = (format && this.format) || void 0

        if (type === ContentType.FormData && body && body !== null && typeof body === "object") {
            requestParams.headers.common = { Accept: "*/*" }
            requestParams.headers.post = {}
            requestParams.headers.put = {}

            body = this.createFormData(body as Record<string, unknown>)
        }

        return this.instance
            .request({
                ...requestParams,
                headers: {
                    ...(type && type !== ContentType.FormData ? { "Content-Type": type } : {}),
                    ...(requestParams.headers || {})
                },
                params: query,
                responseType: responseFormat,
                data: body,
                url: path
            })
            .then(response => response.data)
    }
}

/**
 * @title BKRM Retail Chain Management
 * @version 0.0.1
 * @baseUrl http://localhost:8000/api
 *
 * L5 Swagger API for BKRM Retail Chain Management
 */
export class Api<SecurityDataType extends unknown> extends HttpClient<SecurityDataType> {
    branch = {
        /**
         * No description
         *
         * @tags Branch
         * @name GetBranches
         * @summary Get branches
         * @request GET:/branch
         */
        getBranches: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<Branch[], any>({
                path: `/branch`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Branch
         * @name CreateBranch
         * @summary Create a new branch
         * @request POST:/branch
         */
        createBranch: (data: CreateBranchInput, params: RequestParams = {}) =>
            this.request<Branch, any>({
                path: `/branch`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Branch
         * @name GetBranchImage
         * @summary Get branch image
         * @request GET:/branch/image/{image_key}
         */
        getBranchImage: (imageKey: string, params: RequestParams = {}) =>
            this.request<void, any>({
                path: `/branch/image/${imageKey}`,
                method: "GET",
                format: "blob",
                ...params
            }),

        /**
         * No description
         *
         * @tags Branch
         * @name GetBranch
         * @summary Get branch
         * @request GET:/branch/{branch_id}
         */
        getBranch: (branchId: number, params: RequestParams = {}) =>
            this.request<BranchDetail, any>({
                path: `/branch/${branchId}`,
                method: "GET",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Branch
         * @name UpdateBranch
         * @summary Update branch
         * @request PUT:/branch/{branch_id}
         */
        updateBranch: (branchId: any, data: UpdateBranchInput, params: RequestParams = {}) =>
            this.request<Branch, any>({
                path: `/branch/${branchId}`,
                method: "PUT",
                body: data,
                type: ContentType.FormData,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Branch
         * @name DeleteBranch
         * @summary Delete branch
         * @request DELETE:/branch/{branch_id}
         */
        deleteBranch: (branchId: any, params: RequestParams = {}) =>
            this.request<{ message?: string }, any>({
                path: `/branch/${branchId}`,
                method: "DELETE",
                format: "json",
                ...params
            })
    }
    category = {
        /**
         * No description
         *
         * @tags Category
         * @name GetItemCategories
         * @summary Get item categories
         * @request GET:/category
         */
        getItemCategories: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<Category[], any>({
                path: `/category`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Category
         * @name CreateCategory
         * @summary Create a new item category
         * @request POST:/category
         */
        createCategory: (data: CreateCategoryInput, params: RequestParams = {}) =>
            this.request<Category, any>({
                path: `/category`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Category
         * @name GetCategory
         * @summary Get an item category
         * @request GET:/category/{category_id}
         */
        getCategory: (categoryId: number, params: RequestParams = {}) =>
            this.request<Category, any>({
                path: `/category/${categoryId}`,
                method: "GET",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Category
         * @name UpdateCategory
         * @summary Update an item category
         * @request PUT:/category/{category_id}
         */
        updateCategory: (categoryId: number, data: UpsertCategoryInput, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/category/${categoryId}`,
                method: "PUT",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Category
         * @name DeleteCategory
         * @summary Delete an item category
         * @request DELETE:/category/{category_id}
         */
        deleteCategory: (categoryId: number, query?: { force?: boolean }, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/category/${categoryId}`,
                method: "DELETE",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Category
         * @name GetDeletedCategories
         * @summary Get deleted item categories
         * @request GET:/category/deleted
         */
        getDeletedCategories: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<Category[], any>({
                path: `/category/deleted`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Category
         * @name RestoreCategory
         * @summary Restore an item category
         * @request POST:/category/{category_id}/restore
         */
        restoreCategory: (categoryId: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/category/${categoryId}/restore`,
                method: "POST",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Category
         * @name ForceDeleteCategory
         * @summary Force delete an item category
         * @request DELETE:/category/{category_id}/force
         */
        forceDeleteCategory: (categoryId: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/category/${categoryId}/force`,
                method: "DELETE",
                format: "json",
                ...params
            })
    }
    customer = {
        /**
         * No description
         *
         * @tags Customer
         * @name GetCustomers
         * @summary Get all customers
         * @request GET:/customer
         */
        getCustomers: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<Customer[], any>({
                path: `/customer`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Customer
         * @name CreateCustomer
         * @summary Create a new customer
         * @request POST:/customer
         */
        createCustomer: (data: CreateCustomerInput, params: RequestParams = {}) =>
            this.request<Customer, any>({
                path: `/customer`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Customer
         * @name GetCustomer
         * @summary Get a customer
         * @request GET:/customer/one
         */
        getCustomer: (query?: { id?: number; code?: string }, params: RequestParams = {}) =>
            this.request<Customer, any>({
                path: `/customer/one`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Customer
         * @name UpdateCustomer
         * @summary Update a customer
         * @request PUT:/customer/{customer_id}
         */
        updateCustomer: (customerId: number, data: UpsertCustomerInput, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/customer/${customerId}`,
                method: "PUT",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Customer
         * @name AddCustomerPoint
         * @summary Create a customer
         * @request POST:/customer/add-point/{customer_id}
         */
        addCustomerPoint: (customerId: number, data: { point: number }, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/customer/add-point/${customerId}`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Customer
         * @name UseCustomerPoint
         * @summary Use point
         * @request POST:/customer/use-point/{customer_id}
         */
        useCustomerPoint: (customerId: number, data: { point: number }, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/customer/use-point/${customerId}`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            })
    }
    defaultItem = {
        /**
         * No description
         *
         * @tags DefaultItem
         * @name GetDefaultItems
         * @summary Get all default items
         * @request GET:/default-item
         */
        getDefaultItems: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number; category_id?: number },
            params: RequestParams = {}
        ) =>
            this.request<DefaultItemWithCategory[], any>({
                path: `/default-item`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags DefaultItem
         * @name GetDefaultItemByBarcode
         * @summary Get default item by barcode
         * @request GET:/default-item/one
         */
        getDefaultItemByBarcode: (query?: { barcode?: string; id?: number }, params: RequestParams = {}) =>
            this.request<DefaultItemWithCategory, any>({
                path: `/default-item/one`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            })
    }
    employee = {
        /**
         * No description
         *
         * @tags Employee
         * @name GetEmployees
         * @summary Get employees
         * @request GET:/employee
         */
        getEmployees: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number; branch_id?: number },
            params: RequestParams = {}
        ) =>
            this.request<EmployeeWithEmployment[], any>({
                path: `/employee`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name CreateEmployee
         * @summary Create a new employee
         * @request POST:/employee
         */
        createEmployee: (data: CreateEmployeeInput, params: RequestParams = {}) =>
            this.request<Employee, any>({
                path: `/employee`,
                method: "POST",
                body: data,
                type: ContentType.FormData,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name GetEmployee
         * @summary Get employee
         * @request GET:/employee/{employee_id}
         */
        getEmployee: (employeeId: number, params: RequestParams = {}) =>
            this.request<EmployeeWithEmployment, any>({
                path: `/employee/${employeeId}`,
                method: "GET",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name UpdateEmployee
         * @summary Update employee
         * @request PUT:/employee/{employee_id}
         */
        updateEmployee: (employeeId: any, data: UpdateEmployeeInput, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/employee/${employeeId}`,
                method: "PUT",
                body: data,
                type: ContentType.FormData,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name DeleteEmployee
         * @summary Delete employee
         * @request DELETE:/employee/{employee_id}
         */
        deleteEmployee: (employeeId: number, query?: { force?: boolean }, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/employee/${employeeId}`,
                method: "DELETE",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name ChangeEmployeePassword
         * @summary Update employee password
         * @request PUT:/employee/password
         */
        changeEmployeePassword: (
            data: { current_password: string; new_password: string; new_password_confirmation: string },
            params: RequestParams = {}
        ) =>
            this.request<Message, any>({
                path: `/employee/password`,
                method: "PUT",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name GetEmployeeAvatar
         * @summary Get employee avatar
         * @request GET:/employee/avatar/{avatar_key}
         */
        getEmployeeAvatar: (avatarKey: string, params: RequestParams = {}) =>
            this.request<void, any>({
                path: `/employee/avatar/${avatarKey}`,
                method: "GET",
                format: "blob",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name LoginEmployee
         * @summary Login as employee
         * @request POST:/employee/login
         */
        loginEmployee: (data: EmployeeLoginInput, params: RequestParams = {}) =>
            this.request<EmployeeWithEmployment, any>({
                path: `/employee/login`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name LogoutEmployee
         * @summary Logout as employee
         * @request POST:/employee/logout
         */
        logoutEmployee: (params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/employee/logout`,
                method: "POST",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name GetCurrentEmployee
         * @summary Get employee information
         * @request GET:/employee/me
         */
        getCurrentEmployee: (params: RequestParams = {}) =>
            this.request<EmployeeWithEmployment, any>({
                path: `/employee/me`,
                method: "GET",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name TransferEmployee
         * @summary Transfer employee
         * @request POST:/employee/transfer
         */
        transferEmployee: (data: TransferEmployeeInput, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/employee/transfer`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name GetDeletedEmployees
         * @summary Get deleted employees
         * @request GET:/employee/deleted
         */
        getDeletedEmployees: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<Employee[], any>({
                path: `/employee/deleted`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name RestoreEmployee
         * @summary Restore employee
         * @request POST:/employee/{employee_id}/restore
         */
        restoreEmployee: (employeeId: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/employee/${employeeId}/restore`,
                method: "POST",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Employee
         * @name ForceDeleteEmployee
         * @summary Force delete employee
         * @request DELETE:/employee/{employee_id}/force
         */
        forceDeleteEmployee: (employeeId: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/employee/${employeeId}/force`,
                method: "DELETE",
                format: "json",
                ...params
            })
    }
    item = {
        /**
         * No description
         *
         * @tags Item
         * @name GetAllItems
         * @summary Get all items
         * @request GET:/item
         */
        getAllItems: (
            query?: { search?: string; from?: number; to?: number; order_by?: string; order_type?: "asc" | "desc" },
            params: RequestParams = {}
        ) =>
            this.request<Item[], any>({
                path: `/item`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Item
         * @name CreateItem
         * @summary Create new item
         * @request POST:/item
         */
        createItem: (data: CreateItemInput, params: RequestParams = {}) =>
            this.request<Item, any>({
                path: `/item`,
                method: "POST",
                body: data,
                type: ContentType.FormData,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Item
         * @name GetItemById
         * @summary Get item by id
         * @request GET:/item/one
         */
        getItemById: (query?: { id?: number; barcode?: number }, params: RequestParams = {}) =>
            this.request<ItemWithCategory, any>({
                path: `/item/one`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Item
         * @name GetSelling
         * @summary Get all selling items
         * @request GET:/item/selling
         */
        getSelling: (
            query?: { search?: string; from?: number; to?: number; order_by?: string; order_type?: "asc" | "desc"; branch_id?: number },
            params: RequestParams = {}
        ) =>
            this.request<ItemWithProperties[], any>({
                path: `/item/selling`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * @description Move item from default to current
         *
         * @tags Item
         * @name MoveItem
         * @summary Move item
         * @request POST:/item/move
         */
        moveItem: (data: { barcode: string }, params: RequestParams = {}) =>
            this.request<Item, any>({
                path: `/item/move`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Item
         * @name UpdateItem
         * @summary Update item
         * @request PUT:/item/{item_id}
         */
        updateItem: (itemId: number, data: UpsertItemInput, params: RequestParams = {}) =>
            this.request<Item, any>({
                path: `/item/${itemId}`,
                method: "PUT",
                body: data,
                type: ContentType.FormData,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Item
         * @name DeleteItem
         * @summary Delete item
         * @request DELETE:/item/{item_id}
         */
        deleteItem: (itemId: number, query?: { force?: boolean }, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/item/${itemId}`,
                method: "DELETE",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Item
         * @name GetDeletedItems
         * @summary Get deleted items
         * @request GET:/item/deleted
         */
        getDeletedItems: (
            query?: { search?: string; from?: number; to?: number; order_by?: string; order_type?: "asc" | "desc" },
            params: RequestParams = {}
        ) =>
            this.request<Item[], any>({
                path: `/item/deleted`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Item
         * @name RestoreItem
         * @summary Restore item
         * @request POST:/item/{item_id}/restore
         */
        restoreItem: (itemId: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/item/${itemId}/restore`,
                method: "POST",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Item
         * @name ForceDeleteItem
         * @summary Force delete item
         * @request DELETE:/item/{item_id}/force
         */
        forceDeleteItem: (itemId: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/item/${itemId}/force`,
                method: "DELETE",
                format: "json",
                ...params
            })
    }
    permission = {
        /**
         * @description Update a permission
         *
         * @tags Permission
         * @name UpdatePermission
         * @summary Update a permission
         * @request PUT:/permission/{permission_id}
         */
        updatePermission: (permissionId: number, data: { role_ids: number[] }, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/permission/${permissionId}`,
                method: "PUT",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            })
    }
    purchaseSheet = {
        /**
         * No description
         *
         * @tags PurchaseSheet
         * @name GetPurchaseSheets
         * @summary Get all purchase sheets
         * @request GET:/purchase-sheet
         */
        getPurchaseSheets: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<PurchaseSheetWithSupplierAndEmployee[], any>({
                path: `/purchase-sheet`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags PurchaseSheet
         * @name CreatePurchaseSheet
         * @summary Create a new purchase sheet
         * @request POST:/purchase-sheet
         */
        createPurchaseSheet: (data: CreatePurchaseSheetInput, params: RequestParams = {}) =>
            this.request<PurchaseSheet, any>({
                path: `/purchase-sheet`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags PurchaseSheet
         * @name GetPurchaseSheet
         * @summary Get purchase sheet by id
         * @request GET:/purchase-sheet/{purchase_sheet_id}
         */
        getPurchaseSheet: (purchaseSheetId: number, params: RequestParams = {}) =>
            this.request<PurchaseSheetDetail, any>({
                path: `/purchase-sheet/${purchaseSheetId}`,
                method: "GET",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags PurchaseSheet
         * @name DeletePurchaseSheet
         * @summary Delete purchase sheet
         * @request DELETE:/purchase-sheet/{purchase_sheet_id}
         */
        deletePurchaseSheet: (purchaseSheetId: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/purchase-sheet/${purchaseSheetId}`,
                method: "DELETE",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags PurchaseSheet
         * @name UpdatePurchaseSheetNote
         * @summary Update purchase sheet note
         * @request PUT:/purchase-sheet/{purchase_sheet_id}/note
         */
        updatePurchaseSheetNote: (purchaseSheetId: number, data: { note: string }, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/purchase-sheet/${purchaseSheetId}/note`,
                method: "PUT",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            })
    }
    quantityCheckingSheet = {
        /**
         * @description Get quantity checking sheets
         *
         * @tags QuantityCheckingSheet
         * @name GetQuantityCheckingSheets
         * @summary Get quantity checking sheets
         * @request GET:/quantity-checking-sheet
         */
        getQuantityCheckingSheets: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<QuantityCheckingSheetWithEmployee[], any>({
                path: `/quantity-checking-sheet`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags QuantityCheckingSheet
         * @name CreateQuantityCheckingSheet
         * @summary Create a new quantity checking sheet
         * @request POST:/quantity-checking-sheet
         */
        createQuantityCheckingSheet: (data: CreateQuantityCheckingSheetInput, params: RequestParams = {}) =>
            this.request<QuantityCheckingSheet, any>({
                path: `/quantity-checking-sheet`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * @description Get quantity checking sheet
         *
         * @tags QuantityCheckingSheet
         * @name GetQuantityCheckingSheet
         * @summary Get quantity checking sheet
         * @request GET:/quantity-checking-sheet/{id}
         */
        getQuantityCheckingSheet: (id: number, params: RequestParams = {}) =>
            this.request<QuantityCheckingSheetDetail, any>({
                path: `/quantity-checking-sheet/${id}`,
                method: "GET",
                format: "json",
                ...params
            }),

        /**
         * @description Delete quantity checking sheet
         *
         * @tags QuantityCheckingSheet
         * @name DeleteQuantityCheckingSheet
         * @summary Delete quantity checking sheet
         * @request DELETE:/quantity-checking-sheet/{id}
         */
        deleteQuantityCheckingSheet: (id: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/quantity-checking-sheet/${id}`,
                method: "DELETE",
                format: "json",
                ...params
            })
    }
    role = {
        /**
         * No description
         *
         * @tags Role
         * @name GetRoles
         * @summary Get roles
         * @request GET:/role
         */
        getRoles: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<Role[], any>({
                path: `/role`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Role
         * @name CreateRole
         * @summary Create a new role
         * @request POST:/role
         */
        createRole: (data: CreateRoleInput, params: RequestParams = {}) =>
            this.request<Role, any>({
                path: `/role`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Role
         * @name GetRole
         * @summary Get role by id
         * @request GET:/role/{id}
         */
        getRole: (id: number, params: RequestParams = {}) =>
            this.request<Role, any>({
                path: `/role/${id}`,
                method: "GET",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Role
         * @name UpdateRole
         * @summary Update role
         * @request PUT:/role/{id}
         */
        updateRole: (id: number, data: UpsertRoleInput, params: RequestParams = {}) =>
            this.request<Role, any>({
                path: `/role/${id}`,
                method: "PUT",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Role
         * @name DeleteRole
         * @summary Delete role
         * @request DELETE:/role/{id}
         */
        deleteRole: (id: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/role/${id}`,
                method: "DELETE",
                format: "json",
                ...params
            })
    }
    shift = {
        /**
         * No description
         *
         * @tags Shift
         * @name GetShifts
         * @summary Get all shifts
         * @request GET:/shift
         */
        getShifts: (
            query?: { branch_id?: number; search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<Shift[], any>({
                path: `/shift`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * @description Create a shift (by admin or manager)
         *
         * @tags Shift
         * @name CreateShift
         * @summary Create a new shift
         * @request POST:/shift
         */
        createShift: (data: CreateShiftInput, params: RequestParams = {}) =>
            this.request<Shift, any>({
                path: `/shift`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Shift
         * @name GetShift
         * @summary Get a shift
         * @request GET:/shift/{shift_id}
         */
        getShift: (shiftId: number, params: RequestParams = {}) =>
            this.request<Shift, any>({
                path: `/shift/${shiftId}`,
                method: "GET",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Shift
         * @name UpdateShift
         * @summary Update a shift
         * @request PUT:/shift/{shift_id}
         */
        updateShift: (shiftId: number, data: UpsertShiftInput, params: RequestParams = {}) =>
            this.request<Shift, any>({
                path: `/shift/${shiftId}`,
                method: "PUT",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Shift
         * @name DeleteShift
         * @summary Delete a shift
         * @request DELETE:/shift/{shift_id}
         */
        deleteShift: (shiftId: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/shift/${shiftId}`,
                method: "DELETE",
                format: "json",
                ...params
            })
    }
    store = {
        /**
         * No description
         *
         * @tags Store
         * @name RegisterStore
         * @summary Register a new store
         * @request POST:/store/register
         */
        registerStore: (data: RegisterStoreInput, params: RequestParams = {}) =>
            this.request<Store, any>({
                path: `/store/register`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Store
         * @name LoginStore
         * @summary Login as store owner
         * @request POST:/store/login
         */
        loginStore: (data: LoginStoreInput, params: RequestParams = {}) =>
            this.request<Store, any>({
                path: `/store/login`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Store
         * @name LogoutStore
         * @summary Logout as store owner
         * @request POST:/store/logout
         */
        logoutStore: (params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/store/logout`,
                method: "POST",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Store
         * @name GetStore
         * @summary Get store information
         * @request GET:/store/me
         */
        getStore: (params: RequestParams = {}) =>
            this.request<Store, any>({
                path: `/store/me`,
                method: "GET",
                format: "json",
                ...params
            })
    }
    guard = {
        /**
         * No description
         *
         * @tags Guard
         * @name GetGuard
         * @summary Get guard
         * @request GET:/guard
         */
        getGuard: (params: RequestParams = {}) =>
            this.request<{ guard: "store" | "employee" | "guest" }, any>({
                path: `/guard`,
                method: "GET",
                format: "json",
                ...params
            })
    }
    supplier = {
        /**
         * No description
         *
         * @tags Supplier
         * @name GetSuppliers
         * @summary Get all suppliers
         * @request GET:/supplier
         */
        getSuppliers: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<Supplier[], any>({
                path: `/supplier`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Supplier
         * @name CreateSupplier
         * @summary Create a new supplier
         * @request POST:/supplier
         */
        createSupplier: (data: CreateSupplierInput, params: RequestParams = {}) =>
            this.request<Supplier, any>({
                path: `/supplier`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Supplier
         * @name GetSupplier
         * @summary Get a supplier
         * @request GET:/supplier/{supplier_id}
         */
        getSupplier: (supplierId: number, params: RequestParams = {}) =>
            this.request<Supplier, any>({
                path: `/supplier/${supplierId}`,
                method: "GET",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Supplier
         * @name UpdateSupplier
         * @summary Update a supplier
         * @request PUT:/supplier/{supplier_id}
         */
        updateSupplier: (supplierId: number, data: UpdateSupplierInput, params: RequestParams = {}) =>
            this.request<Supplier, any>({
                path: `/supplier/${supplierId}`,
                method: "PUT",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Supplier
         * @name DeleteSupplier
         * @summary Delete a supplier
         * @request DELETE:/supplier/{supplier_id}
         */
        deleteSupplier: (supplierId: number, query?: { force?: boolean }, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/supplier/${supplierId}`,
                method: "DELETE",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Supplier
         * @name GetDeletedSuppliers
         * @summary Get all soft deleted suppliers
         * @request GET:/supplier/deleted
         */
        getDeletedSuppliers: (
            query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
            params: RequestParams = {}
        ) =>
            this.request<Supplier[], any>({
                path: `/supplier/deleted`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Supplier
         * @name RestoreSupplier
         * @summary Restore a supplier
         * @request POST:/supplier/{supplier_id}/restore
         */
        restoreSupplier: (supplierId: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/supplier/${supplierId}/restore`,
                method: "POST",
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Supplier
         * @name ForceDeleteSupplier
         * @summary Force delete a supplier
         * @request DELETE:/supplier/{supplier_id}/force
         */
        forceDeleteSupplier: (supplierId: number, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/supplier/${supplierId}/force`,
                method: "DELETE",
                format: "json",
                ...params
            })
    }
    workSchedule = {
        /**
         * No description
         *
         * @tags Work Schedule
         * @name GetWorkSchedules
         * @summary Get work schedules
         * @request GET:/work-schedule
         */
        getWorkSchedules: (query?: { date?: string }, params: RequestParams = {}) =>
            this.request<WorkScheduleWithShiftAndEmployee[], any>({
                path: `/work-schedule`,
                method: "GET",
                query: query,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Work Schedule
         * @name CreateWorkSchedule
         * @summary Create a work schedule
         * @request POST:/work-schedule
         */
        createWorkSchedule: (data: CreateWorkScheduleInput, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/work-schedule`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Work Schedule
         * @name UpdateWorkSchedule
         * @summary Update a work schedule
         * @request PUT:/work-schedule/{id}
         */
        updateWorkSchedule: (workScheduleId: number, id: string, data: UpdateWorkScheduleInput, params: RequestParams = {}) =>
            this.request<WorkSchedule, any>({
                path: `/work-schedule/${id}`,
                method: "PUT",
                body: data,
                type: ContentType.Json,
                format: "json",
                ...params
            }),

        /**
         * No description
         *
         * @tags Work Schedule
         * @name DeleteWorkSchedule
         * @summary Delete a work schedule
         * @request DELETE:/work-schedule/{id}
         */
        deleteWorkSchedule: (workScheduleId: number, id: string, params: RequestParams = {}) =>
            this.request<Message, any>({
                path: `/work-schedule/${id}`,
                method: "DELETE",
                format: "json",
                ...params
            })
    }
}
