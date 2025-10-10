<?php

namespace Phannp\Resources;

class Tools extends Resource
{
    /**
     * Generate a QR code image URL with specified data and size.
     *
     * @link https://www.stannp.com/us/direct-mail-api/tools#qrcode
     *
     * @param string $data The data to encode in the QR code
     * @param int $size The size of the QR code image (default: 200)
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function qrcodeCreate(string $data, int $size = 200): array
    {
        return $this->client->get('qrcode/create', [
            'data' => $data,
            'size' => $size,
        ]);
    }

    /**
     * Merge multiple PDF files into a single PDF.
     *
     * @link https://www.stannp.com/us/direct-mail-api/tools#merge-pdf
     *
     * @param array $files An array of file paths or URLs to merge
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function pdfMerge(array $files): array
    {
        // Client-side validation: require a non-empty array of strings
        if (empty($files)) {
            throw new \InvalidArgumentException('files must be a non-empty array of file paths or URLs');
        }

        foreach ($files as $f) {
            if (!is_string($f) || trim($f) === '') {
                throw new \InvalidArgumentException('each file must be a non-empty string');
            }
        }

        return $this->client->get('pdf/merge', [
            'files' => $files,
        ]);
    }

    /**
     * Retrieve a list of available templates.
     *
     * @link https://www.stannp.com/us/direct-mail-api/tools#templates
     *
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function getTemplates(): array
    {
        return $this->client->get('templates/list');
    }
}
