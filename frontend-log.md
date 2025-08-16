Loading categories from /admin/file-categories/list
file-management:2716 Loading categories from /admin/file-categories/list
file-management:2719 Response status: 200
file-management:2723 === API RESPONSE DEBUG ===
file-management:2724 Categories API response: {success: true, data: Array(4)}data: (4) [{…}, {…}, {…}, {…}]success: true[[Prototype]]: Object
file-management:2725 Data structure: {
  "success": true,
  "data": [
    {
      "id": "a7d3bfb0-0e7a-4dca-a4fd-aa0859360184",
      "name": "Images",
      "description": "Image files including photos, screenshots, and graphics",
      "allowed_extensions": [
        "jpg",
        "jpeg",
        "png",
        "gif",
        "webp",
        "svg",
        "bmp",
        "tiff"
      ],
      "max_file_size": 10485760,
      "resource_type": "image",
      "is_active": true,
      "requires_optimization": true,
      "cloudinary_options": {
        "quality": "auto",
        "fetch_format": "auto",
        "folder": "grit-files/images"
      },
      "sort_order": 1,
      "created_at": null,
      "updated_at": "2025-08-16T15:08:19.000000Z"
    },
    {
      "id": "9c1de3ea-8185-4bcd-93d1-2e52e32fee81",
      "name": "Videos",
      "description": "Video files including recordings, presentations, and tutorials",
      "allowed_extensions": [
        "mp4",
        "avi",
        "mov",
        "wmv",
        "flv",
        "webm",
        "3gp",
        "mkv"
      ],
      "max_file_size": 104857600,
      "resource_type": "video",
      "is_active": true,
      "requires_optimization": true,
      "cloudinary_options": {
        "quality": "auto",
        "fetch_format": "auto",
        "folder": "grit-files/videos"
      },
      "sort_order": 2,
      "created_at": null,
      "updated_at": null
    },
    {
      "id": "304d5d48-5a70-40f3-bbbd-0c4fc784668c",
      "name": "Documents",
      "description": "Document files including PDFs, Word, Excel, and PowerPoint files",
      "allowed_extensions": [
        "pdf",
        "doc",
        "docx",
        "xls",
        "xlsx",
        "ppt",
        "pptx",
        "txt",
        "rtf"
      ],
      "max_file_size": 26214400,
      "resource_type": "raw",
      "is_active": true,
      "requires_optimization": false,
      "cloudinary_options": {
        "folder": "grit-files/documents"
      },
      "sort_order": 3,
      "created_at": null,
      "updated_at": null
    },
    {
      "id": "b25d3fdf-518b-4c99-b25c-69c26ba9280d",
      "name": "Archives",
      "description": "Compressed archive files",
      "allowed_extensions": [
        "zip",
        "rar",
        "7z",
        "tar",
        "gz"
      ],
      "max_file_size": 52428800,
      "resource_type": "raw",
      "is_active": true,
      "requires_optimization": false,
      "cloudinary_options": {
        "folder": "grit-files/archives"
      },
      "sort_order": 4,
      "created_at": null,
      "updated_at": null
    }
  ]
}
file-management:2727 Categories data to render: (4) [{…}, {…}, {…}, {…}]
file-management:2728 Number of categories: 4
file-management:2730 First category sample: {id: 'a7d3bfb0-0e7a-4dca-a4fd-aa0859360184', name: 'Images', description: 'Image files including photos, screenshots, and graphics', allowed_extensions: Array(8), max_file_size: 10485760, …}
file-management:2820 Categories data received: (4) [{…}, {…}, {…}, {…}]
file-management:2841 === CATEGORY DEBUG ===
file-management:2842 Processing category: {id: 'a7d3bfb0-0e7a-4dca-a4fd-aa0859360184', name: 'Images', description: 'Image files including photos, screenshots, and graphics', allowed_extensions: Array(8), max_file_size: 10485760, …}
file-management:2843 Category ID: a7d3bfb0-0e7a-4dca-a4fd-aa0859360184
file-management:2844 Category name: Images
file-management:2845 Category allowed_extensions: (8) ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff'] Type: object
file-management:2846 Category is_active: true Type: boolean Value: true
file-management:2847 Category max_file_size: 10485760 Type: number
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2848 isCategoryActive result: true
file-management:2849 === END CATEGORY DEBUG ===
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2841 === CATEGORY DEBUG ===
file-management:2842 Processing category: {id: '9c1de3ea-8185-4bcd-93d1-2e52e32fee81', name: 'Videos', description: 'Video files including recordings, presentations, and tutorials', allowed_extensions: Array(8), max_file_size: 104857600, …}
file-management:2843 Category ID: 9c1de3ea-8185-4bcd-93d1-2e52e32fee81
file-management:2844 Category name: Videos
file-management:2845 Category allowed_extensions: (8) ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', '3gp', 'mkv'] Type: object
file-management:2846 Category is_active: true Type: boolean Value: true
file-management:2847 Category max_file_size: 104857600 Type: number
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2848 isCategoryActive result: true
file-management:2849 === END CATEGORY DEBUG ===
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2841 === CATEGORY DEBUG ===
file-management:2842 Processing category: {id: '304d5d48-5a70-40f3-bbbd-0c4fc784668c', name: 'Documents', description: 'Document files including PDFs, Word, Excel, and PowerPoint files', allowed_extensions: Array(9), max_file_size: 26214400, …}
file-management:2843 Category ID: 304d5d48-5a70-40f3-bbbd-0c4fc784668c
file-management:2844 Category name: Documents
file-management:2845 Category allowed_extensions: (9) ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'] Type: object
file-management:2846 Category is_active: true Type: boolean Value: true
file-management:2847 Category max_file_size: 26214400 Type: number
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2848 isCategoryActive result: true
file-management:2849 === END CATEGORY DEBUG ===
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2841 === CATEGORY DEBUG ===
file-management:2842 Processing category: {id: 'b25d3fdf-518b-4c99-b25c-69c26ba9280d', name: 'Archives', description: 'Compressed archive files', allowed_extensions: Array(5), max_file_size: 52428800, …}
file-management:2843 Category ID: b25d3fdf-518b-4c99-b25c-69c26ba9280d
file-management:2844 Category name: Archives
file-management:2845 Category allowed_extensions: (5) ['zip', 'rar', '7z', 'tar', 'gz'] Type: object
file-management:2846 Category is_active: true Type: boolean Value: true
file-management:2847 Category max_file_size: 52428800 Type: number
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2848 isCategoryActive result: true
file-management:2849 === END CATEGORY DEBUG ===
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2736 === END API RESPONSE DEBUG ===
file-management:2719 Response status: 200
file-management:2723 === API RESPONSE DEBUG ===
file-management:2724 Categories API response: {success: true, data: Array(4)}
file-management:2725 Data structure: {
  "success": true,
  "data": [
    {
      "id": "a7d3bfb0-0e7a-4dca-a4fd-aa0859360184",
      "name": "Images",
      "description": "Image files including photos, screenshots, and graphics",
      "allowed_extensions": [
        "jpg",
        "jpeg",
        "png",
        "gif",
        "webp",
        "svg",
        "bmp",
        "tiff"
      ],
      "max_file_size": 10485760,
      "resource_type": "image",
      "is_active": true,
      "requires_optimization": true,
      "cloudinary_options": {
        "quality": "auto",
        "fetch_format": "auto",
        "folder": "grit-files/images"
      },
      "sort_order": 1,
      "created_at": null,
      "updated_at": "2025-08-16T15:08:19.000000Z"
    },
    {
      "id": "9c1de3ea-8185-4bcd-93d1-2e52e32fee81",
      "name": "Videos",
      "description": "Video files including recordings, presentations, and tutorials",
      "allowed_extensions": [
        "mp4",
        "avi",
        "mov",
        "wmv",
        "flv",
        "webm",
        "3gp",
        "mkv"
      ],
      "max_file_size": 104857600,
      "resource_type": "video",
      "is_active": true,
      "requires_optimization": true,
      "cloudinary_options": {
        "quality": "auto",
        "fetch_format": "auto",
        "folder": "grit-files/videos"
      },
      "sort_order": 2,
      "created_at": null,
      "updated_at": null
    },
    {
      "id": "304d5d48-5a70-40f3-bbbd-0c4fc784668c",
      "name": "Documents",
      "description": "Document files including PDFs, Word, Excel, and PowerPoint files",
      "allowed_extensions": [
        "pdf",
        "doc",
        "docx",
        "xls",
        "xlsx",
        "ppt",
        "pptx",
        "txt",
        "rtf"
      ],
      "max_file_size": 26214400,
      "resource_type": "raw",
      "is_active": true,
      "requires_optimization": false,
      "cloudinary_options": {
        "folder": "grit-files/documents"
      },
      "sort_order": 3,
      "created_at": null,
      "updated_at": null
    },
    {
      "id": "b25d3fdf-518b-4c99-b25c-69c26ba9280d",
      "name": "Archives",
      "description": "Compressed archive files",
      "allowed_extensions": [
        "zip",
        "rar",
        "7z",
        "tar",
        "gz"
      ],
      "max_file_size": 52428800,
      "resource_type": "raw",
      "is_active": true,
      "requires_optimization": false,
      "cloudinary_options": {
        "folder": "grit-files/archives"
      },
      "sort_order": 4,
      "created_at": null,
      "updated_at": null
    }
  ]
}
file-management:2727 Categories data to render: (4) [{…}, {…}, {…}, {…}]
file-management:2728 Number of categories: 4
file-management:2730 First category sample: {id: 'a7d3bfb0-0e7a-4dca-a4fd-aa0859360184', name: 'Images', description: 'Image files including photos, screenshots, and graphics', allowed_extensions: Array(8), max_file_size: 10485760, …}
file-management:2820 Categories data received: (4) [{…}, {…}, {…}, {…}]
file-management:2841 === CATEGORY DEBUG ===
file-management:2842 Processing category: {id: 'a7d3bfb0-0e7a-4dca-a4fd-aa0859360184', name: 'Images', description: 'Image files including photos, screenshots, and graphics', allowed_extensions: Array(8), max_file_size: 10485760, …}
file-management:2843 Category ID: a7d3bfb0-0e7a-4dca-a4fd-aa0859360184
file-management:2844 Category name: Images
file-management:2845 Category allowed_extensions: (8) ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff'] Type: object
file-management:2846 Category is_active: true Type: boolean Value: true
file-management:2847 Category max_file_size: 10485760 Type: number
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2848 isCategoryActive result: true
file-management:2849 === END CATEGORY DEBUG ===
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2841 === CATEGORY DEBUG ===
file-management:2842 Processing category: {id: '9c1de3ea-8185-4bcd-93d1-2e52e32fee81', name: 'Videos', description: 'Video files including recordings, presentations, and tutorials', allowed_extensions: Array(8), max_file_size: 104857600, …}
file-management:2843 Category ID: 9c1de3ea-8185-4bcd-93d1-2e52e32fee81
file-management:2844 Category name: Videos
file-management:2845 Category allowed_extensions: (8) ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', '3gp', 'mkv'] Type: object
file-management:2846 Category is_active: true Type: boolean Value: true
file-management:2847 Category max_file_size: 104857600 Type: number
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2848 isCategoryActive result: true
file-management:2849 === END CATEGORY DEBUG ===
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2841 === CATEGORY DEBUG ===
file-management:2842 Processing category: {id: '304d5d48-5a70-40f3-bbbd-0c4fc784668c', name: 'Documents', description: 'Document files including PDFs, Word, Excel, and PowerPoint files', allowed_extensions: Array(9), max_file_size: 26214400, …}
file-management:2843 Category ID: 304d5d48-5a70-40f3-bbbd-0c4fc784668c
file-management:2844 Category name: Documents
file-management:2845 Category allowed_extensions: (9) ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'] Type: object
file-management:2846 Category is_active: true Type: boolean Value: true
file-management:2847 Category max_file_size: 26214400 Type: number
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2848 isCategoryActive result: true
file-management:2849 === END CATEGORY DEBUG ===
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2841 === CATEGORY DEBUG ===
file-management:2842 Processing category: {id: 'b25d3fdf-518b-4c99-b25c-69c26ba9280d', name: 'Archives', description: 'Compressed archive files', allowed_extensions: Array(5), max_file_size: 52428800, …}
file-management:2843 Category ID: b25d3fdf-518b-4c99-b25c-69c26ba9280d
file-management:2844 Category name: Archives
file-management:2845 Category allowed_extensions: (5) ['zip', 'rar', '7z', 'tar', 'gz'] Type: object
file-management:2846 Category is_active: true Type: boolean Value: true
file-management:2847 Category max_file_size: 52428800 Type: number
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2848 isCategoryActive result: true
file-management:2849 === END CATEGORY DEBUG ===
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:3033 isCategoryActive called with: true Type: boolean
file-management:3043 isActive is boolean true, returning true
file-management:2736 === END API RESPONSE DEBUG ===