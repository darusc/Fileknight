import type { File as FkFile, Folder, FolderContent } from "@/lib/api/core";
import type { Files } from "@/lib/api/files";

export class FileService {

  private files: Files;

  constructor(files: Files) {
    this.files = files;
  }

  public fetchContent(folderId?: string): Promise<FolderContent> {
    return this.files.list(folderId);
  }

  public createFolder(name: string, parentId: string | null) {
    return this.files.createFolder(name, parentId);
  }

  public rename(item: FkFile | Folder, name: string) {
    if ("size" in item) {
      return this.files.update(item.id, { name: name });
    } else {
      return this.files.updateFolder(item.id, { name: name });
    }
  }

  public uploadFiles(files: File[], parentId?: string) {
    const formData = new FormData();
    files.forEach(file => formData.append("file", file));
    formData.append("parentId", parentId ?? "null");

    return this.files.upload(formData);
  }

  public async download(files: string[], folders: string[]) {
    const { blob, filename } = await this.files.download(files, folders);

    const urlObject = window.URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = urlObject;
    a.download = filename;
    document.body.appendChild(a);

    a.click();
    a.remove();
  }
}