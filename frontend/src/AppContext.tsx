import { createContext } from "react";
import { AuthService } from "./services/AuthService";
import { Core as ApiCore } from "./lib/api/core";
import { Auth } from "./lib/api/auth";

type AppServices = {
  auth: AuthService;
}

const apiCore = new ApiCore();

const services: AppServices = {
  auth: new AuthService(new Auth(apiCore)),
}

const AppContext = createContext({} as AppServices);

function AppContextProvider({ children }: { children: React.ReactNode }) {
  return <AppContext.Provider value={services}>{children}</AppContext.Provider>;
}

export { AppContext, AppContextProvider };