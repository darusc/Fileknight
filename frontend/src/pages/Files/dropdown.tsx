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

interface MoreActionsProps {
  selected: ColumnItemType,
  onShowMoreDetails: (item: ColumnItemType) => void
}

export function MoreActionsDropdown({
  selected,
  onShowMoreDetails
}: MoreActionsProps) {
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
          <Download /> Download
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem>
          <Edit /> Rename
        </DropdownMenuItem>
        <DropdownMenuItem>
          <Copy /> Copy
        </DropdownMenuItem>
        <DropdownMenuItem>
          <Star /> Star
        </DropdownMenuItem>
        <DropdownMenuItem onClick={() => onShowMoreDetails(selected)}>
          <Info /> Details
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem className="text-destructive">
          <Trash className="text-destructive" /> Delete
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
}