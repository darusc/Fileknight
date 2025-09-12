import { Outlet } from "react-router-dom";
import { SidebarProvider } from "../ui/sidebar";
import { AppSidebar } from "./app-sidebar";

export default function Main() {
  return (
    <SidebarProvider className="bg-sidebar h-screen">
      <AppSidebar />
      <main className="overflow-hidden flex-grow mt-4 bg-background br-2 rounded-tl-xl shadow-md p flex-1 flex flex-col">
        <Outlet />
      </main>
    </SidebarProvider>    
  )
}