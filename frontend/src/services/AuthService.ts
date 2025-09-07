import { Auth } from "@/lib/api/auth";

export interface LoginCredentials {
  username: string;
  password: string;
}

export interface RegisterCredentials {
  username: string;
  password: string;
  confirmPassword: string;
  token: string;
}

export class AuthService {

  private auth: Auth;

  constructor(auth: Auth) {
    this.auth = auth;
  }

  /**
   * Returns true if login was successful, false otherwise.
   */
  public async login(credentials: LoginCredentials): Promise<boolean> {
    try {
      await this.auth.login(credentials.username, credentials.password);
      return true;
    } catch (error) {
      return false;
    }
  }

  /**
   * Returns the registered user.
   * @throws Error if registration failed.
   */
  public async register(credentials: RegisterCredentials): Promise<void> {
    await this.auth.register(credentials.username, credentials.password, credentials.token);
  }

  public requestPasswordReset(username: string): void {
    this.auth.requestPasswordReset();
  }
}