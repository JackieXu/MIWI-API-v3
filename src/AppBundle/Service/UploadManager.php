<?php


namespace AppBundle\Service;


class UploadManager
{
    public function saveData($name, $content)
    {
        $client = new \Google_Client();
        $client->setApplicationName("MIWI");
        $client->setAuthConfigFile('/var/www/v3/app/config/gcm.json');

        $object = new \Google_Service_Storage_StorageObject();
        $storageService = new \Google_Service_Storage($client);

        $data = $storageService->objects->insert(
            'vurze-1',
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