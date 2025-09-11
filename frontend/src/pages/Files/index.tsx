import { useEffect, useState } from "react"
import { useParams } from "react-router-dom"

import { useFiles } from "@/hooks/appContext"

import Topbar from "@/components/layout/app-topbar"
import { Input } from "@/components/ui/input"
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbSeparator } from "@/components/ui/breadcrumb"
import { Search } from "lucide-react"

import { DataTable } from "./data-table"
import { columns, type ColumnItemType } from "./columns"

export default function FilesPage() {
  const [query, setQuery] = useState("")

  const params = useParams<{ folderId?: string }>();

  const fileService = useFiles();
  const [data, setData] = useState<ColumnItemType[]>([]);

  useEffect(() => {
    fileService.fetchContent(params.folderId).then(result => {
      setData([...result.directories, ...result.files]);
    })
  }, [params]);

  const [path, setPath] = useState<{ id?: string, name: string }[]>([{ name: "Root" }]);

  return (
    <>
      <Topbar>
        <Breadcrumb>
          <BreadcrumbList>
            {path.map((folder, idx) => (
              <BreadcrumbItem key={idx} className={idx === 0 ? "text-primary" : ""}>
                <BreadcrumbLink
                  href="#"
                  onClick={(e) => {
                    // TODO...
                  }}
                >
                  {folder.name}
                </BreadcrumbLink>
                {idx < path.length - 1 && <BreadcrumbSeparator />}
              </BreadcrumbItem>
            ))}
          </BreadcrumbList>
        </Breadcrumb>
      </Topbar>
      <div className="flex flex-1 flex-col">
        <div className="flex flex-1 flex-col gap-2">
          <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6">

            {/* Search bar */}
            <div className="bg-background/95 supports-[backdrop-filter]:bg-background/60 p-4 backdrop-blur">
              <div className="relative max-w-lg w-full">
                <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  type="text"
                  placeholder="Search"
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                  className="pl-10 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                />
              </div>
            </div>

            <DataTable columns={columns} data={data} setPath={setPath}></DataTable>

            <div className="fixed bottom-0 w-full h-10 border-t px-4 py-2 text-sm text-muted-foreground">
              <span>{data.length} items</span>
            </div>
          </div>
        </div>
      </div>
    </>
  )
}
