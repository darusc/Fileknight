import { Outlet } from "react-router-dom";
import { SidebarProvider, SidebarTrigger } from "../ui/sidebar";
import { AppSidebar } from "./app-sidebar";

export default function Main() {
  return (
    <SidebarProvider>
      <AppSidebar />
      <main>
        <SidebarTrigger/>
        <Outlet />
      </main>
    </SidebarProvider>    
  )
}