import { format } from "date-fns";

export function formatBytes(bytes: number): string {
  if (bytes === 0) return "0 B";

  const units = ["B", "KB", "MB", "GB", "TB", "PB"];
  let i = 0;
  let value = bytes;

  // Keep dividing by 1000 until value < 1000 or we run out of units
  while (value >= 1000 && i < units.length - 1) {
    value /= 1000;
    i++;
  }

  return `${value.toFixed(2)} ${units[i]}`;
}

export function formatDate(timestamp: number, fmt: string = "dd MMM yyyy HH:ss"): string {
  return format(new Date(timestamp * 1000), fmt);
}