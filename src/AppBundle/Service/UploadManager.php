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
            file_get_contents("/var/www/v3/app/config/gcm.json")
        );
        $client->setAssertionCredentials($credential);

        $object = new \Google_Service_Storage_StorageObject();
        $storageService = new \Google_Service_Storage($client);

        $data = $storageService->objects->insert(
            'vurze-store-1',
            $object,
            array(
                'name' => $name,
                'data' => $content,
                'uploadType' => 'media'
            )
        );

        return $data->getMediaLink();
    }
}