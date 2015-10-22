<?php


namespace AppBundle\Service;


class UploadManager
{
    public function saveData($name, $content)
    {
        $client = new \Google_Client();
        $client->setApplicationName("Vurze");


        $credential = new \Google_Auth_AssertionCredentials(
            "583057551622-lro22lks59qlb0g0nghjj5rfiikcapmg@developer.gserviceaccount.com",
            ['https://www.googleapis.com/auth/devstorage.read_write'],
            file_get_contents("/var/www/v3/app/config/Vurze-e003d965269e.p12")
        );
        $client->setAssertionCredentials($credential);

        $object = new \Google_Service_Storage_StorageObject();
        $storageService = new \Google_Service_Storage($client);

        $file = finfo_open();
        $mimeType = finfo_buffer($file, $content, FILEINFO_MIME_TYPE);
        finfo_close($file);

        $mimeArray = explode('/', $mimeType);
        $extension = array_pop($mimeArray);

        $object->setContentType($mimeType);

        $data = $storageService->objects->insert(
            'vurze-storage-1',
            $object,
            array(
                'name' => $name.'.'.$extension,
                'data' => $content,
                'uploadType' => 'media',
                'predefinedAcl' => 'publicRead'
            )
        );

        return 'https://vurze-storage-1.storage.googleapis.com/'.$data->getName();
    }
}