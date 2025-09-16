import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function splitBy<T>(
  arr: T[],
  predicate: (item: T) => boolean
): [T[], T[]] {
  return arr.reduce<[T[], T[]]>(
    ([pass, fail], item) =>
      predicate(item) ? [[...pass, item], fail] : [pass, [...fail, item]],
    [[], []]
  );
}
