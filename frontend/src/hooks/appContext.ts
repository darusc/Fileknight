import { AppContext } from "@/AppContext";
import { useContext } from "react";

export function useAppContext() {
  return useContext(AppContext);
}

export function useAuth() {
  const { auth } = useAppContext();
  return auth;
}