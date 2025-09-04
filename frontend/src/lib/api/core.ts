export type HTTPMethod = 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';

/**
 * Interface representing a generic API request.
 */
export interface AdditionalRequestOptions {
  /**
   * Query parameters to be appended to the URL.
   */
  query?: Record<string, string | number | boolean>;
  /**
   * Request body, typically for POST, PUT, PATCH methods.
   */
  body?: any;
  /**
   * Additional headers to include in the request.
   */
  headers?: Record<string, string>;
}

/**
 * Standard structure for API responses.
 * @param T - Type of the data returned in the response.
 */
export interface APIResponse<T> {
  /**
   * Indicates if the API request was successful.
   */
  success: boolean;
  /**
   * HTTP status code returned by the API.
   */
  status: number;
  /**
    * Message providing additional information about the API response.
   */
  message: string;
  /**
   * Data returned by the API, if any (and successful).
   */
  data?: T;
  /**
   * The error code, if the request failed.
   */
  error?: string;
  /**
   * Additional details about the error, if available.
   */
  details?: any;
}

export class ApiError extends Error {
  readonly status: number
  readonly errorCode?: string
  readonly message: string;
  readonly details?: any;

  constructor(apiResponse: APIResponse<any>) {
    super(apiResponse.error + ": " + apiResponse.message);
    this.status = apiResponse.status;
    this.errorCode = apiResponse.error;
    this.message = apiResponse.message;
    this.details = apiResponse.details;
  }
}

/**
 * Core API class that handles base functionality for API interactions.
 */
export class Core {

  private lastApiResponse: APIResponse<any> | null = null;

  private jwtTokenProvider: () => string | null = () => null;

  public setJwtTokenProvider(provider: () => string | null) {
    this.jwtTokenProvider = provider;
  }

  public getJwtToken(): string | null {
    return this.jwtTokenProvider();
  }

  /**
   * Returns the last API response received, or null if no requests have been made yet.
   */
  public getLastApiResponse(): APIResponse<any> | null {
    return this.lastApiResponse;
  }

  /**
   * Convenience method GET request.
   * Returns the response data directly, or throws an APIError on failure.
   */
  public get<T = any>(endpoint: string, options?: AdditionalRequestOptions): Promise<T> {
    return this.request<T>('GET', endpoint, options);
  }

  /**
   * Convenience method for POST request.
   * Returns the response data directly, or throws an APIError on failure.
   */
  public post<T = any>(endpoint: string, options?: AdditionalRequestOptions): Promise<T> {
    return this.request<T>('POST', endpoint, options);
  }

  /**
   * Convenience method for PUT request.
   * Returns the response data directly, or throws an APIError on failure.
   */
  public put<T = any>(endpoint: string, options?: AdditionalRequestOptions): Promise<T> {
    return this.request<T>('PUT', endpoint, options);
  }

  /**
   * Convenience method for PATCH request.
   * Returns the response data directly, or throws an APIError on failure.
   */
  public patch<T = any>(endpoint: string, options?: AdditionalRequestOptions): Promise<T> {
    return this.request<T>('PATCH', endpoint, options);
  }

  /**
   * Convenience method for DELETE request.
   * Returns the response data directly, or throws an APIError on failure.
   */
  public delete<T = any>(endpoint: string, options?: AdditionalRequestOptions): Promise<T> {
    return this.request<T>('DELETE', endpoint, options);
  }

  /**
   * Builds a complete URL with query parameters if provided.
   */
  private buildURL(endpoint: string, query?: Record<string, string | number | boolean>): string {
    let url = endpoint;
    if (query) {
      url += '?';
      Object.entries(query).forEach(([key, value]) => {
        url += `${encodeURIComponent(key)}=${encodeURIComponent(String(value))}&`;
      });
    }
    return url;
  }

  /**
   * Makes an HTTP request to the specified endpoint 
   * with the given method and additional request data.
   * @throws APIError
   */
  private async request<T = any>(method: HTTPMethod, endpoint: string, info: AdditionalRequestOptions = {}): Promise<T> {
    const { query, body, headers } = info;
    const url = this.buildURL(endpoint, query);

    const response = await fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        ...headers
      },
      body: body ? JSON.stringify(body) : undefined,
    });

    // Parse the JSON response into the APIResponse interface
    const apiResponse = await response.json() as APIResponse<T>;
    // If the response indicates failure, throw an APIError
    if (!apiResponse.success) {
      // Throw an ApiError with the response details and log it
      const error = new ApiError(apiResponse);
      console.error(error);
      throw error;
    }

    // Store the last API response
    this.lastApiResponse = apiResponse;

    return (apiResponse.data || {}) as T;
  }
}

export interface User {
  id: number;
  username: string;
  email: string;
  roles: string[];
  created_at: string;
}

export interface File {
  id: string;
  name: string;
  size: number;
  extension: string;
  created_at: number;
  updated_at: number;
}

export interface Folder {
  id: string;
  name: string;
  created_at: number;
  updated_at: number;
}

export interface Session {
  token: string;
  issued_at: number;
  user_agent: string;
  ip_address: string;
  device_id: string;
}

export interface JWTTokenData {
  jwt: string;
  iat: number;
  exp: number;
  refresh_token: string;
}