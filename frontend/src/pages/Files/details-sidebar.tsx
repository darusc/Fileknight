import { formatBytes, formatDate } from "@/lib/formatting";
import type { ColumnItemType } from "./columns";

import { Button } from "@/components/ui/button";
import { File, Folder, X } from "lucide-react";

export default function DetailsSidebar({ selectedFile, onClose }: { selectedFile: ColumnItemType, onClose: () => void }) {
  return (
    <div className="w-80 flex-shrink-0 bg-sidebar shadow-lg transition-all duration-300 border-l z-10">
      <div className="p-4">
        <Button
          variant="ghost" className="sticky"
          onClick={onClose}
        >
          <X />
        </Button>
        <div className="flex flex-col items-center">
          {("size" in selectedFile) ? <File size="5rem" fill="currentColor" /> : <Folder size="5rem" fill="currentColor" />}
          <span className="mt-2">{selectedFile.name}</span>
        </div>
        <div className="flex flex-col gap-2 mt-4">
          <div className="flex justify-between">
            <span className="text-muted-foreground">Type</span>
            <span>{("size" in selectedFile) ? selectedFile.mimeType : "Folder"}</span>
          </div>
          {("size" in selectedFile) &&
            <div className="flex justify-between">
              <span className="text-muted-foreground">Extension</span>
              <span>{("size" in selectedFile) ? selectedFile.extension : "Folder"}</span>
            </div>
          }
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
  );
}