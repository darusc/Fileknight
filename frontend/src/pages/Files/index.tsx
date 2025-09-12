import { useEffect, useState } from "react"
import { useParams } from "react-router-dom"

import { useFiles } from "@/hooks/appContext"
import { splitBy } from "@/lib/utils"

import Topbar from "@/components/layout/app-topbar"
import { Input } from "@/components/ui/input"
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbSeparator } from "@/components/ui/breadcrumb"
import { File, Folder, Plus, Search, Upload, X } from "lucide-react"

import { DataTable } from "./data-table"
import { columns, type ColumnItemType } from "./columns"
import { Button } from "@/components/ui/button"
import { formatBytes, formatDate } from "@/lib/formatting"

export default function FilesPage() {
  const fileService = useFiles();

  const params = useParams<{ folderId?: string }>();

  const [query, setQuery] = useState("")
  const [data, setData] = useState<ColumnItemType[]>([]);
  const [path, setPath] = useState<{ id?: string, name: string }[]>([{ name: "Root" }]);

  const [selectedFile, setSelectedFile] = useState<ColumnItemType | null>(null);

  useEffect(() => {
    fileService.fetchContent(params.folderId).then(result => {
      setData([...result.directories, ...result.files]);
    })
  }, [params]);

  const sort = (key: string, desc: boolean) => {
    const [folders, files] = splitBy(data, (item) => !("size" in item));

    if (key === "name") {
      folders.sort((a, b) => a.name.localeCompare(b.name) * (desc ? -1 : 1));
      files.sort((a, b) => a.name.localeCompare(b.name) * (desc ? -1 : 1));
    } else if (key === "updated") {
      folders.sort((a, b) => (a.updatedAt - b.updatedAt) * (desc ? -1 : 1));
      files.sort((a, b) => (a.updatedAt - b.updatedAt) * (desc ? -1 : 1));
    }

    setData([...folders, ...files]);
  }

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
      <div className="flex flex-1">
        <div className={`flex-1 transition-all duration-300 overflow-auto`}>
          <div className="flex flex-col gap-2 p-4">
            <div className="flex justify-between w-full">

              <div className="flex-grow bg-background/95 supports-[backdrop-filter]:bg-background/60 px-4 backdrop-blur">
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

              <div className="flex gap-2">
                <Button><Plus />New Folder</Button>
                <Button><Upload />Upload</Button>
              </div>
            </div>

            <DataTable
              columns={columns}
              data={data}
              setPath={setPath}
              onSort={sort}
              onShowDetails={setSelectedFile}
            />

            <div className="fixed bottom-0 w-full h-10 border-t px-4 py-2 text-sm text-muted-foreground">
              <span>{data.length} items</span>
            </div>
          </div>
        </div>

        {selectedFile && (
          <div className="w-80 flex-shrink-0 bg-sidebar shadow-lg transition-all duration-300 border-l z-10">
            <div className="p-4">
              <Button
                variant="ghost" className="sticky"
                onClick={() => setSelectedFile(null)}
              >
                <X />
              </Button>
              <div className="flex flex-col items-center">
                {("size" in selectedFile) ? <File size="5rem" fill="currentColor" /> : <Folder size="5rem" fill="currentColor"/>}
                <span className="mt-2">{selectedFile.name}</span>
              </div>
              <div className="flex flex-col gap-2 mt-4">
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Type</span>
                  <span>{("size" in selectedFile) ? "File" : "Folder"}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Size</span>
                  <span>{("size" in selectedFile) ? formatBytes(selectedFile.size) : "-"}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Last modified</span>
                  <span>{formatDate(selectedFile.updatedAt)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Created</span>
                  <span>{formatDate(selectedFile.createdAt)}</span>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  )
}
