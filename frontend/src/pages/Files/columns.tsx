import type { File, Folder } from "@/lib/api/core";
import { formatBytes, formatDate } from "@/lib/formatting";

import { type ColumnDef, type Row, type RowData } from "@tanstack/react-table";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

import { Button } from "@/components/ui/button";
import {
  ArrowUpDown,
  Copy,
  Download,
  Edit,
  Info,
  MoreVertical,
  Star,
  Trash,
  File as FileIcon,
  Folder as FolderIcon,
} from "lucide-react";

export type ColumnItemType = File | Folder;

declare module "@tanstack/react-table" {
  interface ColumnMeta<TData extends RowData, TValue> {
    sortKey?: string; 
  }
}

export const columns: ColumnDef<ColumnItemType>[] = [
  {
    accessorKey: "name",
    meta: { sortKey: "name" },
    header: () => (
      <Button variant="ghost">
        Name
        <ArrowUpDown className="ml-2 h-4 w-4" />
      </Button>
    ),
    cell: ({ row }: { row: Row<ColumnItemType> }) => {
      const data = row.original;
      const isFolder = !("size" in data); // folders don't have size

      return (
        <div className="flex items-center gap-2">
          {isFolder ? (
            <FolderIcon className="w-4 h-4 text-muted-foreground" />
          ) : (
            <FileIcon className="w-4 h-4 text-muted-foreground" />
          )}
          <span>{data.name}</span>
        </div>
      );
    }
  },
  {
    accessorKey: "extension",
    header: "Type",
    cell: ({ row }: { row: Row<ColumnItemType> }) => {
      const data = row.original;
      if ("extension" in data) return data.extension;
      return "Folder";
    },
  },
  {
    accessorKey: "updatedAt",
    meta: { sortKey: "updated" },
    header: () => (
      <Button variant="ghost">
        Last modified
        <ArrowUpDown className="ml-2 h-4 w-4" />
      </Button>
    ),
    cell: ({ row }: { row: Row<ColumnItemType> }) => {
      const timestamp = row.original.updatedAt;
      return <div>{formatDate(timestamp, "d MMMM yyyy")}</div>;
    }
  },
  {
    id: "size",
    header: "Size",
    cell: ({ row }: { row: Row<ColumnItemType> }) => {
      const data = row.original;
      if ("size" in data) {
        return <div>{formatBytes(data.size)}</div>;
      }
      return <div>-</div>;
    },
  },
  {
    id: "actions",
    cell: () => (
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button variant="ghost" className="h-8 w-8 p-0">
            <span className="sr-only">Open menu</span>
            <MoreVertical className="h-4 w-4" />
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
          <DropdownMenuItem>
            <Info /> Details
          </DropdownMenuItem>
          <DropdownMenuSeparator />
          <DropdownMenuItem className="text-destructive">
            <Trash className="text-destructive" /> Delete
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    ),
  }
];