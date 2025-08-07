declare global {
  interface Window {
    Capacitor?: {
      isNative?: boolean;
      [key: string]: any;
    };
  }
}

// Capacitor Filesystem types
declare module '@capacitor/filesystem' {
  export interface FilesystemPlugin {
    writeFile(options: WriteFileOptions): Promise<WriteFileResult>;
    readFile(options: ReadFileOptions): Promise<ReadFileResult>;
    deleteFile(options: DeleteFileOptions): Promise<DeleteFileResult>;
    mkdir(options: MkdirOptions): Promise<MkdirResult>;
    rmdir(options: RmdirOptions): Promise<RmdirResult>;
    readdir(options: ReaddirOptions): Promise<ReaddirResult>;
    getUri(options: GetUriOptions): Promise<GetUriResult>;
    stat(options: StatOptions): Promise<StatResult>;
    rename(options: RenameOptions): Promise<RenameResult>;
    copy(options: CopyOptions): Promise<CopyResult>;
  }

  export interface WriteFileOptions {
    path: string;
    data: string;
    directory?: Directory;
    recursive?: boolean;
  }

  export interface WriteFileResult {
    uri: string;
  }

  export interface ReadFileOptions {
    path: string;
    directory?: Directory;
  }

  export interface ReadFileResult {
    data: string;
  }

  export interface DeleteFileOptions {
    path: string;
    directory?: Directory;
  }

  export interface DeleteFileResult {}

  export interface MkdirOptions {
    path: string;
    directory?: Directory;
    recursive?: boolean;
  }

  export interface MkdirResult {}

  export interface RmdirOptions {
    path: string;
    directory?: Directory;
    recursive?: boolean;
  }

  export interface RmdirResult {}

  export interface ReaddirOptions {
    path: string;
    directory?: Directory;
  }

  export interface ReaddirResult {
    files: FileInfo[];
  }

  export interface FileInfo {
    name: string;
    type: 'file' | 'directory';
    size: number;
    uri: string;
    mtime?: number;
    ctime?: number;
  }

  export interface GetUriOptions {
    path: string;
    directory?: Directory;
  }

  export interface GetUriResult {
    uri: string;
  }

  export interface StatOptions {
    path: string;
    directory?: Directory;
  }

  export interface StatResult {
    type: 'file' | 'directory';
    size: number;
    uri: string;
    mtime?: number;
    ctime?: number;
  }

  export interface RenameOptions {
    from: string;
    to: string;
    directory?: Directory;
    toDirectory?: Directory;
  }

  export interface RenameResult {}

  export interface CopyOptions {
    from: string;
    to: string;
    directory?: Directory;
    toDirectory?: Directory;
  }

  export interface CopyResult {}

  export enum Directory {
    Documents = 'DOCUMENTS',
    Data = 'DATA',
    Library = 'LIBRARY',
    Cache = 'CACHE',
    External = 'EXTERNAL',
    ExternalStorage = 'EXTERNAL_STORAGE'
  }

  export const Filesystem: FilesystemPlugin;
}

export {};
