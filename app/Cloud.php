<?php namespace App;

use Storage;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

class Cloud {
    public static function mainLocalFolder()
    {
        return Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
    }

    public static function getContents($folder, $sort = false)
    {
        $disk = Storage::disk('s3');
        $files = $disk->files($folder);

        if ($sort == true) {
            /*
                Attenzione: più files possono avere la stessa data di ultima
                modifica (se sono stati caricati a breve intervallo uno
                dall'altro)
            */

            $sorting = [];

            foreach($files as $file) {
                $date = $disk->lastModified($file);
                if (!isset($sorting[$date])) {
                    $sorting[$date] = [];
                }

                $sorting[$date][] = $file;
            }

            krsort($sorting);

            $final = [];

            foreach($sorting as $s) {
                foreach($s as $f)
                    $final[] = $f;
            }

            return $final;
        }
        else {
            return $files;
        }
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

    public static function loadFile($filepath, $folder, $filename)
    {
        $disk = Storage::disk('s3');
        $contents = file_get_contents($filepath);
        $disk->put($folder . '/' . $filename, $contents);
    }

    public static function readFile($folder, $filename)
    {
        $disk = Storage::disk('s3');
        return $disk->get($folder . '/' . $filename);
    }

    public static function localPark($folder, $filename)
    {
        $contents = Cloud::readFile($folder, $filename);
        $path = tempnam(sys_get_temp_dir(), 'download');
        file_put_contents($path, $contents);
        return $path;
    }

    public static function deleteFile($folder, $filename)
    {
        $disk = Storage::disk('s3');
        $disk->delete($folder . '/' . $filename);
    }

    public static function testExistance($name)
    {
        $disk = Storage::disk('s3');
        $pattern = env('MATCHING_RULE', '');

        if (empty($pattern)) {
            if ($disk->exists($name))
                return $name;
            else
                return false;
        }
        else {
            $folder = dirname($name);
            $filename = basename($name);
            $filename = preg_replace($pattern, 'X', $filename);
            $contents = Cloud::getContents($folder);

            foreach($contents as $c) {
                $test = preg_replace($pattern, 'X', basename($c));
                if ($test == $filename)
                    return $c;
            }

            return false;
        }
    }
}
