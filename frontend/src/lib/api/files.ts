import { Core, type FolderContent } from "./core";
import type { File as ApiFile, Folder } from "./core";

/**
 * Files API. Wrapper around `/api/files` endpoint.
 */
export class Files {

  private core: Core;

  constructor(core: Core) {
    this.core = core;
  }

  /**
   * List the content of the directory given by the parentId query param. 
   * If parentId is not given return the content of root directory.
   * 
   * ```
   * GET /api/files?parentId={id}
   * ```   
   */
  public async list(parentId?: string): Promise<FolderContent> {
    const query = parentId ? { parentId: parentId } : undefined
    return await this.core.get<FolderContent>("/api/files", {
      query: query,
      headers: {
        'Authorization': `Bearer ${this.core.getJwtToken()}`
      }
    });
  }

  /**
   * Upload a file. 
   * ```
   * POST /api/files
   * {
   *  file:     <file>
   *  parentId: (required) Id of the parent folder. If null, the file will be uploaded to the root directory.
   *  name:     (optional) Optional new name for the file.
   * }
   * ```
   */
  public async upload(formData: FormData): Promise<ApiFile> {
    return await this.core.post<ApiFile>("/api/files", {
      body: formData,
      headers: {
        'Authorization': `Bearer ${this.core.getJwtToken()}`
      }
    });
  }

  /**
   * Update file - rename / move
   * ```
   * PATCH /api/files/{id}
   * {
   *    parentId: (optional) The file's new parent folder. If null new parent is root
   *    name:     (optional) The file's new name
   * }
   * ```
   * @param file Id of the file to update.
   * @param updated Object containing the fields (optional) to update.
   * @returns 
   */
  public async update(file: string, updated: { parentId?: string, name?: string }): Promise<ApiFile> {
    return await this.core.patch<ApiFile>(`/api/files/${file}`, {
      body: updated,
      headers: {
        'Authorization': `Bearer ${this.core.getJwtToken()}`
      }
    });
  }

  /**
   * ```
   * DELETE /api/files/{id}
   * ```
   */
  public async delete(file: string): Promise<void> {
    this.core.delete<void>(`/api/files/${file}`, {
      headers: {
        'Authorization': `Bearer ${this.core.getJwtToken()}`
      }
    });
  }

  /**
   * Creates a new folder.
   * ```
   * POST /api/files/folders
   * {
   *   name:     (required) Folder's name
   *   parentId: (required) Folder's parent. If null, the folder will be created in the root directory.
   * }
   * ```
   * @param name Folder's name
   * @param parentId Folder's parent. If null, the folder will be created in the root directory.
   * @returns 
   */
  public async createFolder(name: string, parentId: string | null): Promise<Folder> {
    return await this.core.post<Folder>("/api/files/folders", {
      body: {
        name: name,
        parentId: parentId
      },
      headers: {
        'Authorization': `Bearer ${this.core.getJwtToken()}`
      }
    });
  }

  /**
   * Update folder - rename / move
   * ```
   * PATCH /api/files/folders/{id}
   * {
   *    parentId: (optional) The folder's new parent folder. If null new parent is root
   *    name:     (optional) The folder's new name
   * }
   * ```
   * @param file Id of the folder to update.
   * @param updated Object containing the fields (optional) to update.
   * @returns 
   */
  public async updateFolder(folder: string, updated: { parentId?: string, name?: string }): Promise<ApiFile> {
    return await this.core.patch<ApiFile>(`/api/files/folders/${folder}`, {
      body: updated,
      headers: {
        'Authorization': `Bearer ${this.core.getJwtToken()}`
      }
    });
  }

  /**
   * ```
   * DELETE /api/files/folders/{id}
   * ```
   */
  public async deleteFolder(folder: string): Promise<void> {
    this.core.delete<void>(`/api/files/folders/${folder}`, {
      headers: {
        'Authorization': `Bearer ${this.core.getJwtToken()}`
      }
    });
  }

  /**
   * Download files and folders as a zip archive.
   * If only one file is requested, it will be downloaded as is.
   * ```
   * POST /api/files/download
   * {
   *  fileIds:   (optional) Array of file ids to download
   *  folderIds: (optional) Array of folder ids to download
   * }
   */
  public async download(fileIds: string[], folderIds: string[]): Promise<{ blob: Blob, filename: string }> {
    return this.core.download("/api/files/download", {
      body: {
        fileIds: fileIds,
        folderIds: folderIds
      },
      headers: {
        'Authorization': `Bearer ${this.core.getJwtToken()}`
      }
    });
  }

  /**
   * Get folder metadata
   * 
   * ```
   * GET /api/files/folders/{id}
   * ```
   */
  public async getMetadata(folderId: string) {
    return this.core.get<{ ancestors: [] }>(`/api/files/folders/${folderId}`)
  }
}