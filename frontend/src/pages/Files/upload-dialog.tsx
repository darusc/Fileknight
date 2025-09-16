import type React from "react"
import { useState } from "react"

import { useFiles } from "@/hooks/appContext"

import { toast } from "sonner"

import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger
} from "@/components/ui/dialog"

import { Check, Loader2, Upload, X } from "lucide-react"

type UploadDialogProps = {
  type: "file" | "folder",
  parentId?: string,
  trigger: React.ReactNode
}

export default function UploadDialog({
  type,
  parentId,
  trigger
}: UploadDialogProps) {

  const fileService = useFiles();
  const [files, setFiles] = useState<File[]>([])
  const [open, setOpen] = useState(false);

  const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault()
    setFiles(Array.from(e.dataTransfer.files))
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFiles(Array.from(e.target.files!))
  }

  const handleUpload = () => {
    if (files.length > 0) {
      const tid = toast("Uploading files...", {
        description: "Please wait while your files are uploaded.",
        duration: Infinity,
        icon: <Loader2 className="h-12 w-12 animate-spin text-primary mb-4" />
      });

      fileService.uploadFiles(files, parentId)
        .then(() => toast("Files uploaded", { icon: <Check /> }))
        .catch(() => toast("Upload failed. Please try again"))
        .finally(() => toast.dismiss(tid));
    }
    setFiles([]);
    setOpen(false);
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger>{trigger}</DialogTrigger>
      <DialogContent className="sm:max-w-lg bg-background">
        <DialogHeader>
          <DialogTitle>Upload Files</DialogTitle>
          <p className="text-sm text-muted-foreground">
            Drag and drop files here or click to select files
          </p>
        </DialogHeader>

        <div
          onDrop={handleDrop}
          onDragOver={(e) => e.preventDefault()}
          className="relative mt-4 flex flex-col items-center justify-center rounded-md border-2 border-dashed border-border p-10 cursor-pointer hover:bg-accent/10 transition-colors"
        >
          <Upload className="w-8 h-8 mb-2 text-muted-foreground" />
          <p className="text-sm text-muted-foreground">
            Click to upload files or drag and drop
          </p>
          <input
            type="file"
            multiple
            className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            onChange={handleChange}
          />
        </div>

        {files.length > 0 && (
          <div className="mt-4">
            <p className="text-sm font-medium mb-2">Files ready to upload:</p>
            <div className="rounded-md border px-2">
              {files.map((file) => (
                <div className="border-b py-2 flex justify-between items-center" key={file.name}>
                  <span>{file.name}</span>
                  <Button variant="ghost"><X /></Button>
                </div>
              ))}
            </div>
          </div>
        )}

        <DialogFooter className="mt-6 flex justify-end gap-2">
          <DialogClose asChild>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button onClick={handleUpload} disabled={files.length === 0}>
            Start Upload
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}