import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Copy, Download, Edit, Info, MoreHorizontal, RotateCcw, Star, Trash } from "lucide-react";

import type { ColumnItemType } from "./columns";
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

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" className="h-8 w-8 p-0">
          <span className="sr-only">Open menu</span>
          <MoreHorizontal className="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        <DropdownMenuItem>
          <RotateCcw/> Restore
        </DropdownMenuItem>
        <DropdownMenuItem>
          <Trash className="text-destructive" /> Delete Permanentely
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
}