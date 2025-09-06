import { createContext } from "react";

type AppServices = {
  
}

const services: AppServices = {

}

const AppContext = createContext({} as AppServices);

function AppContextProvider({ children }: { children: React.ReactNode }) {
  return <AppContext.Provider value={services}>{children}</AppContext.Provider>;
}

export { AppContext, AppContextProvider };