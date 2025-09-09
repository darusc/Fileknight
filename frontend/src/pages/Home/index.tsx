import { Input } from "@/components/ui/input"
import { Card, CardContent } from "@/components/ui/card"
import { Skeleton } from "@/components/ui/skeleton"
import {
  Search,
  FileText,
  HardDrive,
  Folder,
  Users,
  Star,
  Trash2,
  ChevronDown,
  ChevronRight,
} from "lucide-react"
import StatCard from "@/components/custom/stat-card"
import { useState } from "react"

export default function Home() {

  const [filesOpen, setFilesOpen] = useState(true);
  const [foldersOpen, setFoldersOpen] = useState(true);

  return (
    <div className="flex flex-col items-center w-full px-6 py-10 space-y-10">
      {/* Welcome Section */}
      <div className="text-center space-y-2 w-full">
        <h1 className="text-3xl font-bold tracking-tight">Welcome back 👋</h1>
        <p className="text-muted-foreground">
          Manage your files and storage with ease
        </p>
      </div>

      {/* Search Bar */}
      <div className="w-full max-w-2xl">
        <div className="relative">
          <Search className="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground" />
          <Input
            type="text"
            placeholder="Search your files and folders..."
            className="pl-10"
          />
        </div>
      </div>

      {/* Statistics Section */}
      <div className="w-full grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <StatCard title="Total Files" value="1,245" desc="+120 this week" icon={<FileText className="h-5 w-5 text-muted-foreground" />} />
        <StatCard title="Storage Used" value="18.5 GB" desc="of 50 GB" icon={<HardDrive className="h-5 w-5 text-muted-foreground" />} />
        <StatCard title="Folders" value="56" desc="+3 new" icon={<Folder className="h-5 w-5 text-muted-foreground" />} />
        <StatCard title="Shared with Me" value="34" desc="+5 new" icon={<Users className="h-5 w-5 text-muted-foreground" />} />
        <StatCard title="Starred" value="12" desc="Pinned items" icon={<Star className="h-5 w-5 text-muted-foreground" />} />
        <StatCard title="Trash" value="89" desc="Recently deleted" icon={<Trash2 className="h-5 w-5 text-muted-foreground" />} />
      </div>

      <div className="w-full space-y-8">
        {/* Recent Folders */}
        <div className="w-full">
          <div
            className="flex items-center gap-2 justify-start cursor-pointer mb-4"
            onClick={() => setFoldersOpen(!foldersOpen)}
          >
            {foldersOpen ? <ChevronDown /> : <ChevronRight />}
            <h2 className="text-lg font-semibold">Recent Folders</h2>
          </div>
          {foldersOpen && (
            <div className="w-full grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
              {Array.from({ length: 6 }).map((_, idx) => (
                <Card key={idx} className="min-w-[160px] py-3 flex-shrink-0">
                  <CardContent className="flex items-center space-x-2 px-4">
                    <Folder className="w-6 h-6 text-gray-400" />
                    <div className="space-y-1 flex-1">
                      <Skeleton className="h-4" />
                      <Skeleton className="h-3 w-1/2" />
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          )}
        </div>

        {/* Recent Files */}
        <div className="w-full">
          <div
            className="flex items-center gap-2 justify-start cursor-pointer mb-4"
            onClick={() => setFilesOpen(!filesOpen)}
          >
            {filesOpen ? <ChevronDown /> : <ChevronRight />}
            <h2 className="text-lg font-semibold">Recent Files</h2>
          </div>
          {filesOpen && (
            <div className="w-full space-y-2">
              {/* Table header */}
              <div className="grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] gap-4 font-medium px-2 mb-2">
                <span>Name</span>
                <span>Last Opened</span>
                <span>Owner</span>
                <span>Location</span>
              </div>
              <div className="mt-2">
                {Array.from({ length: 8 }).map((_, idx) => (
                  <div
                    key={idx}
                    className="grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] gap-4 px-2 py-4 border-t"
                  >
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-full" />
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}


