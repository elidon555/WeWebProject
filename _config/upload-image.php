<?php

function uploadImage($file, $old_image)
{
    if(empty($file['name'])){
        return $old_image;
    }

    $image = $file['name'];
    $image = "0" . uniqid() . "." . pathinfo($image, PATHINFO_EXTENSION);

    /**
     * Validojme file-in
     */
    $size = $file['size'];
    if ($size > 5242880) {
        echo json_encode(array("status" => 404, "message" => "File cant be bigger than 5 Megabytes" . __LINE__));
        exit;
    }

    /**
     * Percaktojme llojin e files
     */
    $valid_extensions = array(
        'jpg' => "jpg",
        'jpeg' => "jpeg",
        'png' => "png",
    );
    //shikojme cfare extensioni ka
//    $location = "../../_photos/" . $image;
//    $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
    $array_extension = explode('.', $file['name']);
    $imageFileType = end($array_extension);
    if (!isset($valid_extensions[strtolower($imageFileType)])) {
        echo json_encode(array("status" => 404, "message" => "Unsupported file extension" . __LINE__));
        exit;
    }
    /**
     * Ruajme filen ne pathin e percaktuar
     */
    $location = "../_photos/" . $image;
    if (move_uploaded_file($file['tmp_name'], $location)) {

        return $image;
    } else {
        return $old_image;
    }
}

?>