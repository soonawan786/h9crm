<?php

namespace App\Helper;

use App\Models\FileStorage;
use App\Models\StorageSetting;
use Froiden\RestAPI\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class Files
{

    const UPLOAD_FOLDER = 'user-uploads';
    const IMPORT_FOLDER = 'import-files';

    /**
     * @param mixed $image
     * @param string $dir
     * @param null $width
     * @param int $height
     * @param null $crop
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public static function upload($image, string $dir, $width = null, int $height = 800, $crop = null)
    {
        // To upload files to local server
        config(['filesystems.default' => 'local']);

        $uploadedFile = $image;
        $folder = $dir . '/';

        self::validateUploadedFile($uploadedFile);

        $newName = self::generateNewFileName($uploadedFile->getClientOriginalName());

        $tempPath = public_path(self::UPLOAD_FOLDER . '/temp/' . $newName);

        /** Check if folder exits or not. If not then create the folder */
        self::createDirectoryIfNotExist($folder);

        $newPath = $folder . '/' . $newName;

        $uploadedFile->storeAs('temp', $newName);

        if (!empty($crop)) {
            // Crop image
            if (isset($crop[0])) {
                // To store the multiple images for the copped ones
                foreach ($crop as $cropped) {
                    $image = Image::make($tempPath);

                    if (isset($cropped['resize']['width']) && isset($cropped['resize']['height'])) {

                        $image->crop(floor($cropped['width']), floor($cropped['height']), floor($cropped['x']), floor($cropped['y']));

                        $fileName = str_replace('.', '_' . $cropped['resize']['width'] . 'x' . $cropped['resize']['height'] . '.', $newName);
                        $tempPathCropped = public_path(self::UPLOAD_FOLDER . '/temp') . '/' . $fileName;
                        $newPathCropped = $folder . '/' . $fileName;

                        $image->save($tempPathCropped);

                        Storage::put($newPathCropped, File::get($tempPathCropped), ['public']);

                        // Deleting cropped temp file
                        File::delete($tempPathCropped);
                    }

                }
            }
            else {
                $image = Image::make($tempPath);
                $image->crop(floor($crop['width']), floor($crop['height']), floor($crop['x']), floor($crop['y']));
                $image->save();
            }

        }

        if (($width || $height) && \File::extension($uploadedFile->getClientOriginalName()) !== 'svg') {
            // Crop image

            $image = Image::make($tempPath);
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $image->save();
        }

        Storage::put($newPath, File::get($tempPath), ['public']);

        // Deleting temp file
        File::delete($tempPath);


        return $newName;
    }

    public static function validateUploadedFile($uploadedFile)
    {
        if (!$uploadedFile->isValid()) {
            throw new ApiException('File was not uploaded correctly');
        }

        if ($uploadedFile->getClientOriginalExtension() === 'php' || $uploadedFile->getMimeType() === 'text/x-php') {
            throw new ApiException('You are not allowed to upload the php file on server', null, 422, 422, 2023);
        }

        if ($uploadedFile->getClientOriginalExtension() === 'sh' || $uploadedFile->getMimeType() === 'text/x-shellscript') {
            throw new ApiException('You are not allowed to upload the shell script file on server', null, 422, 422, 2023);
        }

        if ($uploadedFile->getClientOriginalExtension() === 'htaccess') {
            throw new ApiException('You are not allowed to upload the htaccess file on server', null, 422, 422, 2023);
        }

        if ($uploadedFile->getClientOriginalExtension() === 'xml') {
            throw new ApiException('You are not allowed to upload XML FILE', null, 422, 422, 2023);
        }

        if ($uploadedFile->getSize() <= 10) {
            throw new ApiException('You are not allowed to upload a file with filesize less than 10 bytes', null, 422, 422, 2023);
        }
    }

    public static function generateNewFileName($currentFileName)
    {
        $ext = strtolower(File::extension($currentFileName));
        $newName = md5(microtime());

        return ($ext === '') ? $newName : $newName . '.' . $ext;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public static function uploadLocalOrS3($uploadedFile, $dir)
    {
        self::validateUploadedFile($uploadedFile);

        if (config('filesystems.default') === 'local') {
            $newName = self::upload($uploadedFile, $dir, false, false, false);

            // Add data to file_storage table
            return self::fileStore($uploadedFile, $dir, $newName);
        }

        // Add data to file_storage table
        $newName = self::fileStore($uploadedFile, $dir);

        // We have given 2 options of upload for now s3 and local
        Storage::disk(config('filesystems.default'))->putFileAs($dir, $uploadedFile, $newName);

        try {
            // Upload files to aws s3 or digitalocean
            Storage::disk(config('filesystems.default'))->missing($dir . '/' . $newName);
        } catch (\Exception $e) {
            throw new \Exception('File not uploaded successfully ');
        }

        return $newName;
    }

    public static function fileStore($file, $folder, $generateNewName = '')
    {

        // Keep $generateNewName empty if you do not want to generate new name
        $newName = ($generateNewName == '') ? self::generateNewFileName($file->getClientOriginalName()) : $generateNewName;
        $setting = StorageSetting::where('status', 'enabled')->first();
        $storageLocation = $setting->filesystem;

        $fileStorage = new FileStorage();
        $fileStorage->filename = $newName;
        $fileStorage->size = $file->getSize();
        $fileStorage->type = $file->getClientMimeType();
        $fileStorage->path = $folder;
        $fileStorage->storage_location = $storageLocation;
        $fileStorage->save();

        return $newName;

    }

    public static function deleteFile($filename, $folder)
    {
        $dir = trim($folder, '/');


        $fileExist = FileStorage::where('filename', $filename)->first();

        if ($fileExist) {
            $fileExist->delete();
        }

        // Delete from Cloud

        if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {

            if (Storage::disk(config('filesystems.default'))->exists($dir . '/' . $filename)) {
                Storage::disk(config('filesystems.default'))->delete($dir . '/' . $filename);
            }

            return true;
        }

        // Delete from Local
        $path = Files::UPLOAD_FOLDER . '/' . $dir . '/' . $filename;

        if (!File::exists(public_path($path))) {
            return true;
        }

        if (File::exists(public_path($path))) {
            try {
                Storage::delete($path);
            } catch (\Throwable) {
                return true;
            }
        }

    }

    public static function deleteDirectory($folder)
    {
        $dir = trim($folder);
        Storage::deleteDirectory($dir);

        return true;
    }

    public static function copy($from, $to)
    {
        Storage::disk(config('filesystems.default'))->copy($from, $to);
    }

    public static function createDirectoryIfNotExist($folder)
    {
        /** Check if folder exits or not. If not then create the folder */
        if (!File::exists(public_path(self::UPLOAD_FOLDER . '/' . $folder))) {
            File::makeDirectory(public_path(self::UPLOAD_FOLDER . '/' . $folder), 0775, true);
        }
    }

}
