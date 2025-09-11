import {
  type ColumnDef,
  flexRender,
  getCoreRowModel,
  getSortedRowModel,
  type SortingState,
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
import { useState } from "react"
import type { ColumnItemType } from "./columns"
import { useNavigate } from "react-router-dom"
import { ScrollArea } from "@/components/ui/scroll-area"


interface DataTableProps<TData, TValue> {
  columns: ColumnDef<TData, TValue>[]
  data: TData[],
  setPath: React.Dispatch<React.SetStateAction<{ id?: string; name: string }[]>>
}

export function DataTable<TData, TValue>({
  columns,
  data,
  setPath
}: DataTableProps<TData, TValue>) {
  const navigate = useNavigate();
  const [sorting, setSorting] = useState<SortingState>([]);

  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    onSortingChange: setSorting,
    getSortedRowModel: getSortedRowModel(),
    state: {
      sorting,
    }
  })

  return (
    <div className="rounded-md px-4">
      <ScrollArea className="w-full h-150">
        <Table className="border-none w-full">
          <TableHeader className="sticky top-0 bg-background">
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id}>
                {headerGroup.headers.map((header, idx) => (
                  <TableHead
                    key={header.id}
                    className={`${idx === 0 ? "text-left" : "text-center"}`}
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
                    if (!("size" in original)) {
                      navigate(`/f/${original.id}`);
                      setPath(prev => [...prev, { id: original.id, name: original.name }]);
                    }
                  }}
                >
                  {row.getVisibleCells().map((cell, idx) => (
                    <TableCell
                      key={cell.id}
                      className={`${idx === 0 ? "w-1/2 text-left pl-5" : "w-1/6 text-center"
                        }`}
                    >
                      {flexRender(cell.column.columnDef.cell, cell.getContext())}
                    </TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow className="border-t last:border-b-0">
                <TableCell
                  colSpan={columns.length}
                  className="h-24 text-center text-left"
                >
                  No results.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </ScrollArea>
    </div>
  );
}