import { Link, useLocation } from "react-router-dom"
import { cn } from "@/lib/utils"
import Logo from "@/assets/logo.png"

import { 
  FileUp, 
  Folder, 
  FolderUp, 
  Home, 
  LucideFolderPlus, 
  Plus, 
  Star, 
  Trash
} from "lucide-react"

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar"


import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"

const navItems = [
  { name: "Home", path: "/home", icon: Home },
  { name: "My Files", path: "/f/", icon: Folder },
  { name: "Bin", path: "/bin", icon: Trash },
]

export function AppSidebar() {

  const location = useLocation();

  return (
    <Sidebar collapsible="icon" className="border-none">
      {/* Header */}
      <SidebarHeader>
        {/* Logo */}
        <div className="border-b flex items-center justify-center pt-4 mb-4">
          <img src={Logo} alt="Logo" />
        </div>
        {/* New dropdown */}
        <SidebarMenu>
          <SidebarMenuItem>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <SidebarMenuButton className={cn(
                  "w-full flex items-center px-4 py-5 rounded-md text-sm font-medium",
                  "bg-primary text-primary-foreground hover:bg-primary/90 hover:text-primary-foreground",
                )}>
                  <Plus className="mr-2 h-4 w-4" />
                  New
                </SidebarMenuButton>
              </DropdownMenuTrigger>
              <DropdownMenuContent className="w-56" align="center">
                <DropdownMenuItem className="justify-between">
                  New Folder
                  <LucideFolderPlus className="ml-2 h-4 w-4" />
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem className="justify-between">
                  Upload File
                  <FileUp className="ml-2 h-4 w-4" />
                </DropdownMenuItem>
                <DropdownMenuItem className="justify-between">
                  Upload Folder
                  <FolderUp className="ml-2 h-4 w-4" />
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      {/* Navigation Content */}
      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupLabel>Navigation</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {navItems.map((item) => {
                const Icon = item.icon
                const active = location.pathname === item.path
                return (
                  <SidebarMenuItem key={item.path}>
                    <SidebarMenuButton asChild isActive={active}>
                      <Link
                        to={item.path}
                        className={cn("flex items-center gap-3")}
                      >
                        <Icon className="h-4 w-4" />
                        <span>{item.name}</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                )
              })}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>
      <SidebarFooter>

      </SidebarFooter>
    </Sidebar>
  )
}