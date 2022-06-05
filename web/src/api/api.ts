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

export type Branch = UpsertTime & { id: number; name: string; address: string };

export type BranchDetail = Branch & { employments: EmploymentDetail[] };

export interface CreateBranchInput {
  name: string;
  address: string;
  transfered_employees?: TransferredEmployeeInput[];
  new_employees?: NewEmployeeInput[];
}

export interface UpdateBranchInput {
  name?: string;
  address?: string;

  /** @format binary */
  image?: File;
}

export interface TransferredEmployeeInput {
  id: number;
  roles: string[];
}

export interface NewEmployeeInput {
  name: string;
  email: string;
  password: string;
  roles: string[];
  phone?: string;
  birthday?: string;
  gender?: string;
}

export type Employee = UpsertTime & {
  id: number;
  name: string;
  email: string;
  store_id: number;
  avatar?: string;
  avatar_key?: string;
  phone?: string;
  birthday?: string;
  gender?: string;
};

export type EmployeeWithEmployment = Employee & { employment: EmploymentWithRoles };

export interface CreateEmployeeInput {
  name: string;
  email: string;

  /** @format password */
  password: string;

  /** @format password */
  password_confirmation: string;
  branch_id: number;
  roles: string[];
  phone?: string;
  birthday?: string;
  gender?: string;
}

export interface EmployeeAvatar {
  /** @format binary */
  avatar?: File;
}

export type CreateSingleEmployeeInput = CreateEmployeeInput & EmployeeAvatar;

export interface CreateManyEmployeesInput {
  employees?: CreateSingleEmployeeInput[];
}

export interface UpdateEmployeeInput {
  name?: string;
  email?: string;
  roles?: string[];
  phone?: string;
  birthday?: string;
  gender?: string;
}

export interface EmployeeLoginInput {
  email: string;

  /** @format password */
  password: string;
  remember?: boolean;
}

export interface TransferEmployeeInput {
  employee_id: number;
  branch_id: number;
  roles: string[];
}

export interface TransferManyEmployeesInput {
  branch_id: number;
  employees: any[];
}

export type Employment = UpsertTime & {
  id: number;
  employee_id: number;
  branch_id: number;
  from: string;
  to: string | null;
};

export type EmploymentWithRoles = Employment & { roles: EmploymentRole[] };

export interface EmploymentRole {
  id: number;
  employment_id: number;
  role: string;
}

export type EmploymentDetail = Employment & { employee: Employee };

export interface UpsertTime {
  created_at: string;
  updated_at: string;
}

export type Store = UpsertTime & { id: number; name: string; email: string };

export type RegisterStoreInput = LoginStoreInput & { name: string; password_confirmation: string };

export interface LoginStoreInput {
  email: string;

  /** @format password */
  password: string;
  remember?: boolean;
}

import axios, { AxiosInstance, AxiosRequestConfig, AxiosResponse, ResponseType } from "axios";

export type QueryParamsType = Record<string | number, any>;

export interface FullRequestParams extends Omit<AxiosRequestConfig, "data" | "params" | "url" | "responseType"> {
  /** set parameter to `true` for call `securityWorker` for this request */
  secure?: boolean;
  /** request path */
  path: string;
  /** content type of request body */
  type?: ContentType;
  /** query params */
  query?: QueryParamsType;
  /** format of response (i.e. response.json() -> format: "json") */
  format?: ResponseType;
  /** request body */
  body?: unknown;
}

export type RequestParams = Omit<FullRequestParams, "body" | "method" | "query" | "path">;

export interface ApiConfig<SecurityDataType = unknown> extends Omit<AxiosRequestConfig, "data" | "cancelToken"> {
  securityWorker?: (
    securityData: SecurityDataType | null,
  ) => Promise<AxiosRequestConfig | void> | AxiosRequestConfig | void;
  secure?: boolean;
  format?: ResponseType;
}

export enum ContentType {
  Json = "application/json",
  FormData = "multipart/form-data",
  UrlEncoded = "application/x-www-form-urlencoded",
}

export class HttpClient<SecurityDataType = unknown> {
  public instance: AxiosInstance;
  private securityData: SecurityDataType | null = null;
  private securityWorker?: ApiConfig<SecurityDataType>["securityWorker"];
  private secure?: boolean;
  private format?: ResponseType;

  constructor({ securityWorker, secure, format, ...axiosConfig }: ApiConfig<SecurityDataType> = {}) {
    this.instance = axios.create({ ...axiosConfig, baseURL: axiosConfig.baseURL || "http://localhost:8000/api" });
    this.secure = secure;
    this.format = format;
    this.securityWorker = securityWorker;
  }

  public setSecurityData = (data: SecurityDataType | null) => {
    this.securityData = data;
  };

  private mergeRequestParams(params1: AxiosRequestConfig, params2?: AxiosRequestConfig): AxiosRequestConfig {
    return {
      ...this.instance.defaults,
      ...params1,
      ...(params2 || {}),
      headers: {
        ...(this.instance.defaults.headers || {}),
        ...(params1.headers || {}),
        ...((params2 && params2.headers) || {}),
      },
    };
  }

  private createFormData(input: Record<string, unknown>): FormData {
    return Object.keys(input || {}).reduce((formData, key) => {
      const property = input[key];
      formData.append(
        key,
        property instanceof Blob
          ? property
          : typeof property === "object" && property !== null
          ? JSON.stringify(property)
          : `${property}`,
      );
      return formData;
    }, new FormData());
  }

  public request = async <T = any, _E = any>({
    secure,
    path,
    type,
    query,
    format,
    body,
    ...params
  }: FullRequestParams): Promise<AxiosResponse<T>> => {
    const secureParams =
      ((typeof secure === "boolean" ? secure : this.secure) &&
        this.securityWorker &&
        (await this.securityWorker(this.securityData))) ||
      {};
    const requestParams = this.mergeRequestParams(params, secureParams);
    const responseFormat = (format && this.format) || void 0;

    if (type === ContentType.FormData && body && body !== null && typeof body === "object") {
      requestParams.headers.common = { Accept: "*/*" };
      requestParams.headers.post = {};
      requestParams.headers.put = {};

      body = this.createFormData(body as Record<string, unknown>);
    }

    return this.instance.request({
      ...requestParams,
      headers: {
        ...(type && type !== ContentType.FormData ? { "Content-Type": type } : {}),
        ...(requestParams.headers || {}),
      },
      params: query,
      responseType: responseFormat,
      data: body,
      url: path,
    });
  };
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
      params: RequestParams = {},
    ) =>
      this.request<Branch[], any>({
        path: `/branch`,
        method: "GET",
        query: query,
        format: "json",
        ...params,
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
        ...params,
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
        ...params,
      }),

    /**
     * No description
     *
     * @tags Branch
     * @name UpdateBranchImage
     * @summary Update branch image
     * @request PUT:/branch/{branch_id}/image
     */
    updateBranchImage: (branchId: number, data: { image?: File }, params: RequestParams = {}) =>
      this.request<{ message?: string; image?: string }, any>({
        path: `/branch/${branchId}/image`,
        method: "PUT",
        body: data,
        type: ContentType.FormData,
        format: "json",
        ...params,
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
        ...params,
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
        ...params,
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
      this.request<Branch, any>({
        path: `/branch/${branchId}`,
        method: "DELETE",
        format: "json",
        ...params,
      }),
  };
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
      query?: { search?: string; order_by?: string; order_type?: "asc" | "desc"; from?: number; to?: number },
      params: RequestParams = {},
    ) =>
      this.request<EmployeeWithEmployment[], any>({
        path: `/employee`,
        method: "GET",
        query: query,
        format: "json",
        ...params,
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
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Employee
     * @name CreateManyEmployees
     * @summary Create many new employees
     * @request POST:/employee/many
     */
    createManyEmployees: (data: CreateManyEmployeesInput, params: RequestParams = {}) =>
      this.request<{ message?: string }, any>({
        path: `/employee/many`,
        method: "POST",
        body: data,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Employee
     * @name UpdateEmployeeAvatar
     * @summary Update employee avatar
     * @request PUT:/employee/{employee_id}/avatar
     */
    updateEmployeeAvatar: (employeeId: any, data: { avatar?: File }, params: RequestParams = {}) =>
      this.request<{ message?: string }, any>({
        path: `/employee/${employeeId}/avatar`,
        method: "PUT",
        body: data,
        type: ContentType.FormData,
        format: "json",
        ...params,
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
        ...params,
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
      this.request<Employee, any>({
        path: `/employee/${employeeId}`,
        method: "PUT",
        body: data,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Employee
     * @name DeleteEmployee
     * @summary Delete employee
     * @request DELETE:/employee/{employee_id}
     */
    deleteEmployee: (employeeId: number, params: RequestParams = {}) =>
      this.request<{ message?: string }, any>({
        path: `/employee/${employeeId}`,
        method: "DELETE",
        format: "json",
        ...params,
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
        ...params,
      }),

    /**
     * No description
     *
     * @tags Employee
     * @name GetEmployeesByBranch
     * @summary Get employees by branch
     * @request GET:/employee/branch/{branch_id}
     */
    getEmployeesByBranch: (branchId: number, params: RequestParams = {}) =>
      this.request<EmployeeWithEmployment[], any>({
        path: `/employee/branch/${branchId}`,
        method: "GET",
        format: "json",
        ...params,
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
        ...params,
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
      this.request<{ message?: string }, any>({
        path: `/employee/logout`,
        method: "POST",
        format: "json",
        ...params,
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
        ...params,
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
      this.request<{ message?: string }, any>({
        path: `/employee/transfer`,
        method: "POST",
        body: data,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Employee
     * @name TransferManyEmployees
     * @summary Transfer many employees
     * @request POST:/employee/transfer/many
     */
    transferManyEmployees: (data: TransferManyEmployeesInput, params: RequestParams = {}) =>
      this.request<{ message?: string }, any>({
        path: `/employee/transfer/many`,
        method: "POST",
        body: data,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),
  };
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
        ...params,
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
        ...params,
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
      this.request<{ message?: string }, any>({
        path: `/store/logout`,
        method: "POST",
        format: "json",
        ...params,
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
        ...params,
      }),
  };
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
      this.request<{ guard?: string }, any>({
        path: `/guard`,
        method: "GET",
        format: "json",
        ...params,
      }),
  };
}
