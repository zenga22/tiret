<?php namespace App;

use Storage;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

class Cloud {

    public static function getContents($folder)
    {
        $disk = Storage::disk('s3');
        return $disk->files($folder);
    }

    public static function createFolder($folder)
    {
        $disk = Storage::disk('s3');
        $disk->makeDirectory($folder);
    }

    public static function deleteFolder($folder)
    {
        $disk = Storage::disk('s3');
        $disk->deleteDirectory($folder);
    }

    public static function loadFile($folder, $filepath)
    {
        $disk = Storage::disk('s3');
        $name = basename($filepath);
        $contents = file_get_contents($filepath);
        $disk->put($folder . '/' . $name, $contents);
    }

    public static function readFile($folder, $filename)
    {
        $disk = Storage::disk('s3');
        return $disk->get($folder . '/' . $filename);
    }

    public static function deleteFile($folder, $filename)
    {
        $disk = Storage::disk('s3');
        $disk->delete($folder . '/' . $filename);
    }

    public static function testExistance($name)
    {
        $disk = Storage::disk('s3');
        return $disk->exists($name);
    }

}
