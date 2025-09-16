import type React from "react";
import { SidebarTrigger } from "../ui/sidebar";

export default function Topbar({ children }: { children: React.ReactNode }) {
  return (
    <header className="group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 flex h-12 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
      <div className="flex w-full items-center gap-1 px-4 lg:gap-2 lg:px-6">
        <SidebarTrigger />
        <span data-orientation="vertical" className="shrink-0 bg-border h-full w-[1px] mx-2 data-[orientation=vertical]:h-4"></span>
        {children}
      </div>
    </header>
  )
}