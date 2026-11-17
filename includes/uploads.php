<?php

function get_image_extension(string $path): string
{
    if (!is_file($path)) {
        throw new InvalidArgumentException('Choose a JPG, PNG, GIF, or WebP image smaller than 5 MB.');
    }

    $size = filesize($path);
    if ($size === false || $size > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('Choose a JPG, PNG, GIF, or WebP image smaller than 5 MB.');
    }

    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    $image = @getimagesize($path);

    if (!is_string($mime) || !isset($extensions[$mime])
        || !is_array($image) || ($image['mime'] ?? null) !== $mime
        || $image[0] < 1 || $image[0] > 6000 || $image[1] < 1 || $image[1] > 6000
    ) {
        throw new InvalidArgumentException('Choose a JPG, PNG, GIF, or WebP image smaller than 5 MB.');
    }

    return $extensions[$mime];
}

function save_uploaded_image(array $file): string
{
    $path = $file['tmp_name'] ?? null;
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_string($path) || !is_uploaded_file($path)) {
        throw new InvalidArgumentException('The image upload could not be verified.');
    }

    $extension = get_image_extension($path);
    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!is_dir($directory) || !move_uploaded_file($path, $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('The image could not be saved.');
    }

    return 'uploads/' . $filename;
}
