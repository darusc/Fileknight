import { useFiles } from "@/hooks/appContext";
import type { ColumnItemType } from "./columns";

import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { MoreHorizontal, RotateCcw, Trash } from "lucide-react";

interface MoreActionsProps {
  selected: ColumnItemType,
  onShowMoreDetails: (item: ColumnItemType) => void
}

export function MoreActionsDropdown({
  selected,
  onShowMoreDetails
}: MoreActionsProps) {

  const fileService = useFiles();

  const onRestore = () => {
    const isFile = "size" in selected;
    const fileIds = isFile ? [selected.id] : [];
    const folderIds = !isFile ? [selected.id] : [];

    fileService.restoreFromBin(fileIds, folderIds);
  }

  const onDelete = () => {
    const isFile = "size" in selected;
    const fileIds = isFile ? [selected.id] : [];
    const folderIds = !isFile ? [selected.id] : [];

    fileService.deleteFromBin(fileIds, folderIds);
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
        <DropdownMenuItem onClick={onRestore}>
          <RotateCcw /> Restore
        </DropdownMenuItem>
        <DropdownMenuItem onClick={onDelete}>
          <Trash className="text-destructive" /> Delete Permanentely
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
}