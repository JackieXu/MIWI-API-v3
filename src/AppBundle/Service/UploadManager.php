<?php


namespace AppBundle\Service;


class UploadManager
{
    public function saveData($name, $content)
    {
        $client = new \Google_Client();
        $client->setApplicationName("MIWI");


        $credential = new \Google_Auth_AssertionCredentials(
            "202539044446-tfa6611ea0v491evalvpclarcos5822h@developer.gserviceaccount.com",
            ['https://www.googleapis.com/auth/devstorage.read_write'],
            file_get_contents("/var/www/v3/app/config/gcm.p12")
        );
        $client->setAssertionCredentials($credential);

        $object = new \Google_Service_Storage_StorageObject();
        $storageService = new \Google_Service_Storage($client);

        $file = finfo_open();
        $mimeType = finfo_buffer($file, $content, FILEINFO_MIME_TYPE);
        finfo_close($file);

        $mimeArray = explode('/', $mimeType);
        $extension = array_pop($mimeArray);

        $data = $storageService->objects->insert(
            'vurze-store-1',
            $object,
            array(
                'name' => $name.'.'.$extension,
                'data' => $content,
                'uploadType' => 'multipart',
                'predefinedAcl' => 'publicRead',
                'contentType' => $mimeType
            )
        );

        return 'https://vurze-store-1.storage.googleapis.com/'.$data->getName();
    }
}