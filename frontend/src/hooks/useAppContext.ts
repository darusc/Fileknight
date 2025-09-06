import { AppContext } from "@/AppContext";
import { useContext } from "react";

export default function useAppContext() {
  return useContext(AppContext);
}