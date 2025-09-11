import { createContext, useEffect, useState } from "react";

import { Auth } from "./lib/api/auth";
import { Core as ApiCore } from "./lib/api/core";
import { Files } from "./lib/api/files";
import { AuthService } from "./services/AuthService";
import { FileService } from "./services/FileService";

import Loading from "./pages/Loading";

type AppServices = {
  auth: AuthService;
  file: FileService
}

const apiCore = new ApiCore();
const apiAuth = new Auth(apiCore);
const apiFiles = new Files(apiCore)

const services: AppServices = {
  auth: new AuthService(apiAuth),
  file: new FileService(apiFiles)
}

const AppContext = createContext({} as AppServices);

function AppContextProvider({ children }: { children: React.ReactNode }) {

  const [ready, setReady] = useState(false);

  // On context provider mount, initialize the auth service
  // This will check for existing tokens and set the auth state accordingly
  useEffect(() => {
    apiAuth.initialize().then(() => setReady(true));
  }, []);

  if (!ready) {
    return <Loading />;
  }

  return <AppContext.Provider value={services}>{children}</AppContext.Provider>;
}

export { AppContext, AppContextProvider };