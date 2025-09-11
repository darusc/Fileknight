import type { FolderContent } from "@/lib/api/core";
import type { Files } from "@/lib/api/files";

export class FileService {

  private files: Files;

  constructor(files: Files) {
    this.files = files;
  }

  public fetchContent(folderId?: string): Promise<FolderContent> {
    return this.files.list(folderId);
  }
}