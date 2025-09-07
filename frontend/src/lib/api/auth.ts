import { Core, type JWTTokenData, type User, type Session } from "./core";

/**
 * Authentication API. Wrapper around `/api/auth` endpoint.
 */
export class Auth {

  /** 
   * Define static endpoints for authentication-related actions
   */
  static readonly endpoints = {
    register: "/api/auth/register",
    login: "/api/auth/login",
    logout: "/api/auth/logout",
    logoutAll: "/api/auth/logout/all",
    refresh: "/api/auth/refresh",
    requestPasswordReset: (userId: string) => `/api/auth/${userId}/reset`,
    requestChangePassword: (userId: string) => `/api/auth/${userId}/edit/password`
  };

  private core: Core;

  /**
   * Holds the current JWT token data, or null if not authenticated.
   */
  private jwt: JWTTokenData | null = null;

  /**
   * Device ID for the current session, used for login and logout
   */
  private deviceId: string = "";

  constructor(core: Core) {
    this.core = core;
    // Set the core's JWT token provider to return the current token
    this.core.setJwtTokenProvider(() => this.getToken());
  }

  /**
   * Initializes the Auth service by checking for existing tokens in local storage.
   * If a refresh token is found, it attempts to refresh the JWT token.
   * This should be called once on application startup.
   */
  public async initialize(): Promise<void> {
    // Create new or retrieve a unique device ID for this client
    const devId = localStorage.getItem('device_id');
    if (devId) {
      this.deviceId = devId;
    } else {
      this.deviceId = crypto.randomUUID();
      localStorage.setItem('device_id', this.deviceId);
    }

    // On initialization, check for a stored refresh token
    // and attempt to obtain a new JWT token
    const refreshToken = localStorage.getItem('refresh_token');
    if (refreshToken) {
      await this.refresh(refreshToken);
    }
  }

  public isAuthenticated(): boolean {
    return this.jwt !== null;
  }

  public getToken(): string | null {
    return this.jwt ? this.jwt.jwt : null;
  }

  /**
   * This is used for finishing the registration process. 
   * The user entity is created by the server's admin, 
   * this api just sets finished registration (sets the password) using the received token
   * ```
   * POST /api/auth/register
   * {
   *   username: (required) User's unique username
   *   password: (required) User's password
   *   token:    (required) Token used for registration. Received from server admin
   * }
   * ```
   * @param username User's unique username
   * @param password User's password
   * @param token - Registration token
   */
  async register(username: string, password: string, token: string): Promise<User> {
    return await this.core.post<User>(Auth.endpoints.register, {
      body: {
        'username': username,
        'password': password,
        'token': token
      }
    });
  }

  /**
   * ```
   * POST /api/auth/login
   * {
   *     username:
   *     password:
   * }
   * ```
   * @param username User's unique username
   * @param password User's password
   */
  async login(username: string, password: string): Promise<boolean> {
    const result = await this.core.post<JWTTokenData>(Auth.endpoints.login, {
      body: {
        'username': username,
        'password': password
      },
      headers: {
        'Fk-Device-Id': this.deviceId
      }
    });

    this.store(result);
    return true;
  }

  /**
   * Requests a password reset for the currently logged-in user.
   * ```
   * POST /api/auth/{userId}/reset
   * ```
   */
  async requestPasswordReset(): Promise<void> {
    // Get the user ID from the JWT payload
    const userId = this.getJWTPayload(this.jwt!.jwt)['user_id'];
    this.core.post<void>(Auth.endpoints.requestPasswordReset(userId));
  }

  /**
   * Changes the password for the currently logged-in user.
   * ```
   * PATCH /api/auth/{userId}/edit/password
   * {
   *     oldPassword: (required) User's old password
   *     newPassword: (required) User's new password
   * }
   * ```
   */
  async changePassword(currentPassword: string, newPassword: string): Promise<void> {
    // Get the user ID from the JWT payload
    const userId = this.getJWTPayload(this.jwt!.jwt)['user_id'];
    this.core.patch<void>(Auth.endpoints.requestChangePassword(userId), {
      body: {
        'oldPassword': currentPassword,
        'newPassword': newPassword
      },
      headers: {
        'Authorization': `Bearer ${this.getToken()}`
      }
    });
  }

  /**
   * Logs out the use from the current device 
   * by invalidating the refresh token on the server
   * and clearing the local jwt store.
   * ```
   * POST /api/auth/logout
   * Headers: Fk-Device-Id
   * ```
   */
  async logout(): Promise<void> {
    this.core.post<void>(Auth.endpoints.logout, {
      headers: {
        'Authorization': `Bearer ${this.getToken()}`,
        'Fk-Device-Id': this.deviceId
      }
    });
    this.clear();
  }

  /**
   * Logs out the user from all devices by invalidating all refresh tokens
   * ```
   * POST /api/auth/logout/all
   * ``` 
  */
  async logoutAll(): Promise<void> {
    this.core.post<void>(Auth.endpoints.logoutAll, {
      headers: {
        'Authorization': `Bearer ${this.getToken()}`
      }
    });
    this.clear();
  }

  /**
   * ```
   * GET /api/auth/sessions
   * ```
   */
  async sessions(): Promise<Session[]> {
    return await this.core.get<Session[]>(`/api/auth/sessions`, {
      headers: {
        'Authorization': `Bearer ${this.getToken()}`
      }
    });
  }

  /**
   * Refresh the JWT token using the provided refresh token.
   * Refresh is automatically called before the token expires.
   * If refresh fails, the user is logged out. 
   * ```
   * POST /api/auth/refresh
   * {
   *     refresh_token: (required) The refresh token
   * }
   * ```
   */
  private async refresh(refreshToken: string) {
    try {
      const result = await this.core.post<JWTTokenData>(Auth.endpoints.refresh, {
        body: {
          'refresh_token': refreshToken
        }
      });
      this.store(result);
    } catch (error) {
      this.clear();
    }
  }

  /**
   * Stores the auth data and schedules token refresh.
   */
  private store(tokenData: JWTTokenData) {
    // Store the JWT token data in memory for future use
    this.jwt = tokenData;
    // Persist the refresh token in local storage for session continuity
    localStorage.setItem('refresh_token', tokenData.refresh_token);

    // Schedule automatic token refresh slightly before expiration
    setTimeout(() => {
      this.refresh(tokenData.refresh_token);
    }, tokenData.exp * 1000 - Date.now() - 120000); // Refresh 2 minutes before expiry
  }

  /**
   * Clears the auth data from memory and local storage.
   */
  private clear() {
    this.jwt = null;
    localStorage.removeItem('refresh_token');
  }

  /**
   * Gets the payload of a JWT token.
   */
  private getJWTPayload(jwt: string): any {
    const payloadB64 = jwt.split('.')[1];
    const payloadDecoded = atob(payloadB64.replace(/-/g, '+').replace(/_/g, '/'));

    return JSON.parse(payloadDecoded);
  }
}