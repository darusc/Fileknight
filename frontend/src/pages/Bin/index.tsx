import { DataTable } from "@/components/data-table";
import Topbar from "@/components/layout/app-topbar";

import { columns, type ColumnItemType } from "./columns"
import { useFiles } from "@/hooks/appContext";
import { useEffect, useState } from "react";
import { Search } from "lucide-react";
import { Input } from "@/components/ui/input";
import { splitBy } from "@/lib/utils";

export default function Bin() {

  const fileService = useFiles();

  const [query, setQuery] = useState("");
  const [data, setData] = useState<ColumnItemType[]>([]);

  const [detailedItem, setDetailedItem] = useState<ColumnItemType | null>(null);
  const [selectedFiles, setSelectedFiles] = useState<ColumnItemType[]>([]);
  const [clearSelection, setClearSelection] = useState<boolean>(false);

  useEffect(() => {
    if(clearSelection) {
      setClearSelection(false);
    }
  }, [clearSelection]);

  useEffect(() => {
    fileService.fetchBin().then(result => setData([...result.directories, ...result.files]));
  }, []);

  const sort = (key: string, desc: boolean) => {
    const [folders, files] = splitBy(data, (item) => !("size" in item));

    if (key === "name") {
      folders.sort((a, b) => a.name.localeCompare(b.name) * (desc ? -1 : 1));
      files.sort((a, b) => a.name.localeCompare(b.name) * (desc ? -1 : 1));
    } else if (key === "deleted") {
      folders.sort((a, b) => (a.deletedAt - b.deletedAt) * (desc ? -1 : 1));
      files.sort((a, b) => (a.deletedAt - b.deletedAt) * (desc ? -1 : 1));
    }

    setData([...folders, ...files]);
  }

  return (
    <>
      <Topbar>
        <h1>Bin</h1>
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

            </div>

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

        {/* {detailedItem && (
          <DetailsSidebar selectedFile={detailedItem} onClose={() => setDetailedItem(null)} />
        )} */}
      </div>
    </>
  )
}