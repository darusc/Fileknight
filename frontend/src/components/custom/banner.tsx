import type { ReactNode } from "react";

type BannerVariant = "error" | "success" | "info";

interface BannerProps {
  children: ReactNode;
  variant?: BannerVariant;
  className?: string;
}

export function Banner({ children, variant = "info", className = "" }: BannerProps) {
  const baseClasses = "w-full rounded-md border px-4 py-2 text-sm text-center";

  let variantClasses = "";

  switch (variant) {
    case "error":
      variantClasses =
        "bg-red-50 border-red-300 text-red-700 dark:bg-destructive dark:border-destructive dark:text-destructive-foreground";
      break;
    case "success":
      variantClasses =
        "bg-green-50 border-green-300 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200";
      break;
    case "info":
      variantClasses =
        "bg-blue-50 border-blue-300 text-blue-700 dark:bg-muted dark:border-muted dark:text-muted-foreground";
      break;
  }

  return <div className={`${baseClasses} ${variantClasses} ${className}`}>{children}</div>;
}