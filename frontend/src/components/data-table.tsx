import { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"

import type { ColumnItemType } from "../pages/Files/columns"

import {
  type ColumnDef,
  flexRender,
  getCoreRowModel,
  useReactTable,
} from "@tanstack/react-table"

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

import { ScrollArea } from "@/components/ui/scroll-area"
import { FolderPlus } from "lucide-react"

declare module '@tanstack/react-table' {
  interface TableMeta<TData> {
    onShowDetails: (item: TData) => void;
  }
}

interface DataTableProps {
  columns: ColumnDef<ColumnItemType>[]
  data: ColumnItemType[],
  onSort: (key: string, desc: boolean) => void,
  onShowDetails: (item: ColumnItemType) => void,
  onSelectedRowsChange: (rows: ColumnItemType[]) => void,
  clearSelectedRows: boolean,
  navigateOnRowDoubleClick?: boolean
}

type SortConfig = { key: string; desc: boolean } | null;

export function DataTable({
  columns,
  data,
  onSort,
  onShowDetails,
  onSelectedRowsChange,
  clearSelectedRows,
  navigateOnRowDoubleClick
}: DataTableProps) {
  const navigate = useNavigate();

  const [sortConfig, setSortConfig] = useState<SortConfig>(null);
  const [rowSelection, setRowSelection] = useState<Record<string, boolean>>({});

  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    meta: {
      onShowDetails: onShowDetails
    },
    state: {
      rowSelection: rowSelection
    },
    onRowSelectionChange: setRowSelection,
    getRowId: (row) => row.id
  });

  useEffect(() => {
    if(onSelectedRowsChange) {
      const selectedRows = table.getSelectedRowModel().flatRows.map(r => r.original);
      onSelectedRowsChange(selectedRows);
    }
  }, [rowSelection]);

  useEffect(() => {
    if(clearSelectedRows) {
      setRowSelection({});
      onSelectedRowsChange([]);
    }
  }, [clearSelectedRows]);

  return (
    <div className="rounded-md px-4">
      <ScrollArea className="w-full h-180">
        <Table className="border-none w-full">
          <TableHeader className="sticky top-0 bg-background">
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id}>
                {headerGroup.headers.map((header, idx) => (
                  <TableHead
                    key={header.id}
                    className={`
                        ${idx == 0 ? "w-[24px] pl-2" : ""}
                        ${idx == 1 ? "w-1/2 pl-4" : "text-right"} 
                        py-2
                      `}
                    onClick={() => {
                      const key = header.column.columnDef.meta?.sortKey;
                      if (key) {
                        const desc = sortConfig?.key === key ? !sortConfig.desc : false;
                        onSort(key, desc);
                        setSortConfig({ key, desc });
                      }
                    }}
                  >
                    {header.isPlaceholder
                      ? null
                      : flexRender(
                        header.column.columnDef.header,
                        header.getContext()
                      )}
                  </TableHead>
                ))}
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {table.getRowModel().rows?.length ? (
              table.getRowModel().rows.map((row) => (
                <TableRow
                  key={row.id}
                  data-state={row.getIsSelected() && "selected"}
                  className="border-t last:border-b-0"
                  onDoubleClick={() => {
                    const original = row.original as ColumnItemType;
                    if (navigateOnRowDoubleClick && !("size" in original)) {
                      navigate(`/f/${original.id}`);
                    }
                  }}
                >
                  {row.getVisibleCells().map((cell, idx) => (
                    <TableCell
                      key={cell.id}
                      className={`
                        ${idx == 0 ? "w-[24px] pl-2" : ""}
                        ${idx == 1 ? "w-1/2 pl-4" : "text-right"} 
                        py-2
                      `}
                    >
                      {flexRender(cell.column.columnDef.cell, cell.getContext())}
                    </TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow className="border-t last:border-b-0 cursor-default hover:bg-transparent">
                <TableCell colSpan={columns.length}>
                  <div className="flex flex-col items-center justify-center h-[60vh]">
                    <FolderPlus className="mb-4 w-10 h-10 text-muted-foreground" />
                    <span className="text-muted-foreground">This folder is empty</span>
                  </div>
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </ScrollArea>
    </div>
  );
}