<?php


namespace MB;


class FileHandler
{

    static public function deleteFile(string $name): bool
    {
        return \unlink(ROOT_PATH . "/public/$name");
    }

    static public function downloadFromUrl(string $name, string $url)
    {
        return \file_put_contents(ROOT_PATH . "/public/$name", file_get_contents($url));
    }

    static public function getUploadedFile(string $name)
    {
        if (!\array_key_exists($name, $_FILES) || $_FILES[$name]['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        return $_FILES[$name];
    }

    /**
     * Will return null if file not found
     * Will return false if no error
     * Will return error code if error founded
     * @param string $name
     * @return int
     */
    static public function getUploadedFileError(string $name)
    {
        if (!\array_key_exists($name, $_FILES)) {
            return null;
        }
        if ($_FILES[$name]['error'] === UPLOAD_ERR_OK) {
            return false;
        }

        return $_FILES[$name]['error'];
    }

    static public function moveUploadedFile(string $name, string $path = null)
    {
        if (!$file = self::getUploadedFile($name)) {
            return false;
        }

        $filePath = ROOT_PATH . '/public';
        do {
            $fileName = is_null($path) || $path === '' ? '/' . uniqid() . $file['name'] : "/$path/" . uniqid() . $file['name'];
        } while (file_exists($filePath . $fileName));

        move_uploaded_file($file['tmp_name'], $filePath . $fileName);
        return $fileName;
    }

    static public function resizeImage(string $pathFrom, string $pathTo, int $newWidth, int $newHeight)
    {
        $type =
            \mb_strtolower(
                \mb_substr(
                    \mb_strrchr($pathFrom, '.', false, 'UTF-8'),
                    1,
                    null,
                    'UTF-8'
                )
            );

        switch ($type) {
            case 'bmp':
                $image = imagecreatefromwbmp($pathFrom);
                break;
            case 'gif':
                $image = imagecreatefromgif($pathFrom);
                break;
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($pathFrom);
                break;
            case 'png':
                $image = imagecreatefrompng($pathFrom);
                break;
            default :
                return "Unsupported picture type";
        }

        list($width, $height) = \getimagesize($pathFrom);

        $newImage = \imagecreatetruecolor($newWidth, $newHeight);
        \imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return \imagepng($newImage, ROOT_PATH . "/public/$pathTo");
    }

}