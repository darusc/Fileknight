import type { File, Folder } from "@/lib/api/core";
import { formatBytes, formatDate } from "@/lib/formatting";

import { type ColumnDef, type RowData } from "@tanstack/react-table";

import { Button } from "@/components/ui/button";
import {
  ArrowUpDown,
  File as FileIcon,
  Folder as FolderIcon,
} from "lucide-react";

import { Checkbox } from "@/components/ui/checkbox";
import { MoreActionsDropdown } from "./dropdown";

export type ColumnItemType = File | Folder;

declare module "@tanstack/react-table" {
  interface ColumnMeta<TData extends RowData, TValue> {
    sortKey?: string;
  }
}

export const columns: ColumnDef<ColumnItemType>[] = [
  {
    id: "select",
    header: ({ table }) => (
      <Checkbox
        className="cursor-pointer"
        checked={table.getIsAllRowsSelected()}
        onCheckedChange={(checked) => table.toggleAllRowsSelected(!!checked)}
      />
    ),
    cell: ({ row }) => (
      <Checkbox
        className="cursor-pointer"
        checked={row.getIsSelected()}
        onCheckedChange={(checked) => row.toggleSelected(!!checked)}
      />
    )
  },
  {
    accessorKey: "name",
    meta: { sortKey: "name" },
    header: () => (
      <Button variant="ghost">
        Name
        <ArrowUpDown className="ml-2 h-4 w-4" />
      </Button>
    ),
    cell: ({ row }) => {
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
    accessorKey: "mimeType",
    header: "Type",
    cell: ({ row }) => {
      const data = row.original;
      if ("mimeType" in data) return data.mimeType;
      return "Folder";
    },
  },
  {
    accessorKey: "deletedAt",
    meta: { sortKey: "deleted" },
    header: () => (
      <Button variant="ghost">
        Deleted At
        <ArrowUpDown className="ml-2 h-4 w-4" />
      </Button>
    ),
    cell: ({ row }) => {
      const timestamp = row.original.deletedAt;
      return <div>{formatDate(timestamp, "d MMM yyyy HH:ss")}</div>;
    }
  },
  {
    id: "size",
    header: "Size",
    cell: ({ row }) => {
      const data = row.original;
      if ("size" in data) {
        return <div>{formatBytes(data.size)}</div>;
      }
      return <div>-</div>;
    },
  },
  {
    id: "actions",
    cell: ({ row, table }) => (
      <MoreActionsDropdown selected={row.original} onShowMoreDetails={table.options.meta?.onShowDetails || (() => { })} />
    ),
  }
];