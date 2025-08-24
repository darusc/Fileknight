## FILES API (/api/files)

- [List content - GET /api/files?parentId={id}](#list-content)
- [Upload file - POST /api/files](#upload-file)
- [Update file - PATCH /api/files/{id}](#update-file---rename--move)
- [Delete file - DELETE /api/files/{id}](#delete-file)
- [Create folder - POST /api/files/folders](#create-folder)
- [Update folder - PATCH /api/files/folders/{id}](#update-folder---rename--move)
- [Delete folder - DELETE /api/files/folders/{id}](#delete-folder)
- [Download - POST /api/files/download](#download)
- [Error Response](#error-response)


### List content:
```
GET /api/files?parentId={id}
```

```
Sample response:
{
    "success": true,
    "message": "SUCCESS",
    "data": {
        "directories": [
            {
                "id":
                "name":
                "createdAt":
                "updatedAt": 
            }
        ],
        "files": [
            {
                "id":
                "name":
                "size": 
                "extension": 
                "createdAt": 
                "updatedAt":
            }
        ]
    },
    "status": 200
}
```

### Upload file
```
POST /api/files
{
    file:     <file>
    parentId: (required) The folder in which to upload it. If null upload in root
    name:     (optional) The name to upload the file with. If not specified or null use the original file name
}
```
```
Sample response:
{
    "success": true,
    "message": "File uploaded successfully.",
    "data": {
        "id":
        "name":
        "size":
        "extension":
        "createdAt":
        "updatedAt":
    },
    "status": 200
}
```

### Update file - rename / move
```
PATCH /api/files/{id}
{
    parentId: (optional) The file's new parent folder. If null new parent is root
    name:     (optional) The file's new name
}
```
```
Sample response:
{
    "success": true,
    "message": "File updated successfully.",
    "data": {
        "id":
        "name":
        "size":
        "extension":
        "createdAt":
        "updatedAt":
    },
    "status": 200
}
```
                                          
### Delete file
```
DELETE /api/files/{id}
```
```
Sample response:
{
    "success": true,
    "message": "File {id} deleted successfully.",
    "data": [],
    "status": 200
}
```

### Create folder
```
POST /api/files/folders
{
    name:     (required) Folder's name
    parentId: (required) Folder's parent. If null create in root
}
```
```
Sample response:
{
    "success": true,
    "message": "Directory created successfully.",
    "data": {
        "id":
        "name":
        "createdAt":
        "updatedAt":
    },
    "status": 200
}
```

### Update folder - rename / move
```
PATCH /api/files/folders/{id}
{
    parentId: (optional) Folder's new parent folder. If null the new parent will be root
    name:     (optional) Folder's new name
}
```
```
Sample response:
{
    "success": true,
    "message": "Folder updated successfully.",
    "data": {
        "id":
        "name":
        "createdAt":
        "updatedAt":
    },
    "status": 200
}
```

### Delete folder
```
DELETE /api/files/folders/{id}
```
```
Sample response:
{
    "success": true,
    "message": "Folder {id} deleted successfully.",
    "data": [],
    "status": 200
}
```

### Download
```
POST /api/files/download
{
    folderIds: (optional) Array containing the ids of the folders to download
    fileIds:   (optional) Array containing the ids of the files to download
}
```

### Error response
All error responses have the following structure:
```
{
    "success": false,
    "error": "ERROR_CODE",
    "message": "Descriptive error message",
    "details": {
        Object containing specific details about this error
    },
    "status": STATUS_CODE
}
```
