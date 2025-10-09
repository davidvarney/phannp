<?php

namespace Phannp\Resources;

/**
 * Class Files
 *
 * @package Phannp\Resources
 * @link https://www.stannp.com/us/direct-mail-api/files
 */
class Files extends Resource
{
    /**
     * Upload a file
     *
     * @link https://www.stannp.com/us/direct-mail-api/files#uploadFile
     *
     * @param string|resource $file The file path to upload or a resource
     * @param int|null $folder_id Optional folder ID to upload the file into
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function upload($file, ?int $folder_id = null): array
    {
        $data = ['file' => $file];
        if ($folder_id !== null) {
            $data['folder_id'] = $folder_id;
        }

        return $this->client->post('files/upload', $data);
    }

    /**
     * Cfreate a new folder
     *
     * @link https://www.stannp.com/us/direct-mail-api/files#createFolder
     *
     * @param int $id The file ID
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function createFolder(string $name): array
    {
        return $this->client->get("files/get", ['name' => $name]);
    }

    /**
     * Get list of folders
     *
     * @link https://www.stannp.com/us/direct-mail-api/files#listFolders
     *
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function listFolders(): array
    {
        return $this->client->get('files/folders');
    }
}
