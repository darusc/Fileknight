import { useEffect, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
import { useNavigate, useParams } from "react-router-dom"

import { useFiles } from "@/hooks/appContext"
import { splitBy } from "@/lib/utils"

import Topbar from "@/components/layout/app-topbar"
import { Input } from "@/components/ui/input"
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbSeparator } from "@/components/ui/breadcrumb"
import { Download, FileUp, Plus, Search, Star, Trash, Upload, X } from "lucide-react"
import { Download, FileUp, Plus, Search, Star, Trash, Upload, X } from "lucide-react"

import { DataTable } from "./data-table"
import { columns, type ColumnItemType } from "./columns"
import { Button } from "@/components/ui/button"
import DetailsSidebar from "./details-sidebar"
import { type SimpleDialogSubmitData, SimpleDialog } from "./simple-dialog"
import { toast } from "sonner"
import UploadDialog from "./upload-dialog"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

export default function FilesPage() {
  const navigate = useNavigate();
  const navigate = useNavigate();
  const fileService = useFiles();

  const params = useParams<{ folderId?: string }>();

  const [query, setQuery] = useState("")
  const [data, setData] = useState<ColumnItemType[]>([]);
  const [path, setPath] = useState<{ id?: string, name: string }[]>([{name: 'Root'}]);
  const [path, setPath] = useState<{ id?: string, name: string }[]>([{name: 'Root'}]);

  const [detailedItem, setDetailedItem] = useState<ColumnItemType | null>(null);
  const [selectedFiles, setSelectedFiles] = useState<ColumnItemType[]>([]);
  const [clearSelection, setClearSelection] = useState<boolean>(false);

  useEffect(() => {
    if(clearSelection) {
      setClearSelection(false);
    }
  }, [clearSelection]);

  useEffect(() => {
    fileService.fetchContent(params.folderId).then(result => {
      setData([...result.directories, ...result.files]);
    });
    if(params.folderId) {
      fileService.getFolderMetadata(params.folderId).then(result => {
        console.log(result);
        setPath([...result.ancestors.reverse(), {id: params.folderId, name: ''}]);
      });
    } else {
      setPath([{name: 'Root'}]);
    }
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

  const onCreateFolder = (data: SimpleDialogSubmitData) => {
    fileService.createFolder(data.input, params.folderId ?? null)
      .then(() => toast("Folder created successfully"))
      .catch(() => toast("Error creating new folder"));
  }

  const onDownloadSelected = () => {
    const [folders, files] = splitBy(selectedFiles, (item) => !("size" in item));
    fileService.download(files.map(f => f.id), folders.map(f => f.id));
  }

  return (
    <>
      <Topbar>
        <Breadcrumb>
          <BreadcrumbList>
            {path.map((folder, idx) => (
              <BreadcrumbItem key={idx} className={idx === 0 ? "text-primary" : ""}>
                <BreadcrumbLink onClick={() => navigate(`/f/${folder.id ?? ''}`)}>
                <BreadcrumbLink onClick={() => navigate(`/f/${folder.id ?? ''}`)}>
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
                <SimpleDialog
                  title="Create new folder"
                  label="Folder Name"
                  defaultValue="New Folder"
                  onSubmit={onCreateFolder}
                  trigger={
                    <Button><Plus />Create</Button>
                  }
                />

                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button><Upload />Upload</Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent className="flex flex-col">
                    <UploadDialog
                      type="file"
                      parentId={params.folderId}
                      trigger={
                        <DropdownMenuItem onSelect={e => e.preventDefault()}>
                          <FileUp /> Upload file
                        </DropdownMenuItem>
                      }
                    />
                    {/* <UploadDialog
                      type="folder"
                      parentId={params.folderId}
                      trigger={
                        <DropdownMenuItem onSelect={e => e.preventDefault()}>
                          <FolderUp /> Upload folder
                        </DropdownMenuItem>
                      }
                    /> */}
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>

            {selectedFiles.length > 0 && (
              <div className="flex items-center gap-2 bg-surface px-4 py-2 border-b shadow-sm">
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => setClearSelection(true)}
                >
                  <X className="w-4 h-4 mr-1" />
                </Button>
                <span className="text-sm text-muted-foreground font-medium h-5 pr-4 border-r border-secondary">
                  {selectedFiles.length} selected
                </span>

                <Button
                  variant="ghost"
                  size="sm"
                  className="text-muted-foreground"
                  onClick={onDownloadSelected}
                >
                  <Download className="w-4 h-4 mr-1" /> Download
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  className="text-muted-foreground"
                  onClick={() => console.log("Move to Bin", selectedFiles)}
                >
                  <Trash className="w-4 h-4 mr-1" /> Move to Bin
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  className="text-muted-foreground"
                  onClick={() => console.log("Star", selectedFiles)}
                >
                  <Star className="w-4 h-4 mr-1" /> Star
                </Button>
              </div>
            )}

            <DataTable
              columns={columns}
              data={data}
              onSort={sort}
              onShowDetails={item => setDetailedItem(item)}
              onSelectedRowsChange={rows => setSelectedFiles(rows)}
              clearSelectedRows={clearSelection}
            />

            <div className="fixed bottom-0 w-full h-10 border-t px-4 py-2 text-sm text-muted-foreground">
              <span>{data.length} items</span>
            </div>
          </div>
        </div>

        {detailedItem && (
          <DetailsSidebar selectedFile={detailedItem} onClose={() => setDetailedItem(null)} />
        )}
      </div>
    </>
  )
}
