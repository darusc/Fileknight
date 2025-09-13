import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Copy, Download, Edit, Info, MoreHorizontal, Star, Trash } from "lucide-react";

import type { ColumnItemType } from "./columns";
import { SimpleDialog, type SimpleDialogSubmitData } from "./simple-dialog";
import { useFiles } from "@/hooks/appContext";
import { toast } from "sonner";

interface MoreActionsProps {
  selected: ColumnItemType,
  onShowMoreDetails: (item: ColumnItemType) => void
}

export function MoreActionsDropdown({
  selected,
  onShowMoreDetails
}: MoreActionsProps) {

  const fileService = useFiles();

  const onRename = (data: SimpleDialogSubmitData) => {
    const isFile = "size" in selected;
    fileService.rename(selected, data.input)
      .then(() => toast(`${isFile ? 'File' : 'Folder'} "${selected.name}" renamed to "${data.input}"`))
      .catch(() => toast(`${isFile ? 'File' : 'Folder'} renaming failed`));
  }

  const onDownload = () => {
    const isFile = "size" in selected;
    const fileIds = isFile ? [selected.id] : [];
    const folderIds = !isFile ? [selected.id] : [];
    
    fileService.download(fileIds, folderIds);
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" className="h-8 w-8 p-0">
          <span className="sr-only">Open menu</span>
          <MoreHorizontal className="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        <DropdownMenuItem onClick={onDownload}>
          <Download /> Download
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        {/* <Edit /> Rename */}
        <SimpleDialog
          title="Rename"
          label="New name"
          defaultValue={selected.name}
          onSubmit={onRename}
          trigger={
            <DropdownMenuItem onSelect={e => e.preventDefault()}><Edit /> Rename</DropdownMenuItem>
          }
        />
        <DropdownMenuItem>
          <Copy /> Duplicate
        </DropdownMenuItem>
        <DropdownMenuItem>
          <Star /> Star
        </DropdownMenuItem>
        <DropdownMenuItem onClick={() => onShowMoreDetails(selected)}>
          <Info /> Details
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem>
          <Trash className="text-destructive" /> Move to trash
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
}