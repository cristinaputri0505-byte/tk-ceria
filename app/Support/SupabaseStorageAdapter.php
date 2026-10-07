<?php

namespace App\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToListContents;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use Psr\Http\Message\ResponseInterface;

/**
 * Penyimpanan file di Supabase Storage lewat REST API resminya (tanpa SDK tambahan).
 *
 * Dipakai di Vercel karena sistem file server Vercel tidak permanen.
 * Kunci rahasia (SUPABASE_SECRET_KEY) hanya dipakai di server, tidak pernah dikirim ke browser.
 */
class SupabaseStorageAdapter implements FilesystemAdapter
{
    private Client $http;
    private ExtensionMimeTypeDetector $mime;

    public function __construct(
        private string $projectUrl,   // Project URL dari dashboard Supabase
        private string $key,          // Secret key (baru) atau kunci JWT model lama
        private string $bucket,
        private bool $public = false,
        ?Client $http = null,
    ) {
        $this->projectUrl = rtrim($projectUrl, '/');
        $headers = ['apikey' => $key];
        if (str_starts_with($key, 'eyJ')) {
            $headers['Authorization'] = 'Bearer ' . $key; // kunci model lama (JWT)
        }
        $this->http = $http ?? new Client([
            'base_uri' => $this->projectUrl . '/storage/v1/',
            'headers' => $headers,
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => false,
        ]);
        $this->mime = new ExtensionMimeTypeDetector();
    }

    private function enc(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));
    }

    private function ok(ResponseInterface $r): bool
    {
        return $r->getStatusCode() >= 200 && $r->getStatusCode() < 300;
    }

    private function alasan(ResponseInterface $r): string
    {
        return 'Supabase Storage HTTP ' . $r->getStatusCode() . ': ' . mb_substr((string) $r->getBody(), 0, 300);
    }

    /** URL publik (hanya untuk bucket publik: galeri, logo, berita, dll). */
    public function getUrl(string $path): string
    {
        $dasar = rtrim(env('SUPABASE_PUBLIC_URL') ?: $this->projectUrl . '/storage/v1/object/public', '/');
        return $dasar . '/' . $this->bucket . '/' . $this->enc($path);
    }

    public function fileExists(string $path): bool
    {
        try {
            $r = $this->http->head('object/authenticated/' . $this->bucket . '/' . $this->enc($path));
        } catch (GuzzleException $e) {
            throw UnableToCheckExistence::forLocation($path, $e);
        }
        if ($this->ok($r)) return true;
        if (in_array($r->getStatusCode(), [400, 404], true)) return false;
        throw UnableToCheckExistence::forLocation($path, new \RuntimeException($this->alasan($r)));
    }

    public function directoryExists(string $path): bool
    {
        foreach ($this->listContents($path, false) as $_) return true;
        return false;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->unggah($path, $contents);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->unggah($path, stream_get_contents($contents));
    }

    private function unggah(string $path, string $isi): void
    {
        try {
            $r = $this->http->post('object/' . $this->bucket . '/' . $this->enc($path), [
                'body' => $isi,
                'headers' => [
                    'Content-Type' => $this->mime->detectMimeTypeFromPath($path) ?? 'application/octet-stream',
                    'x-upsert' => 'true',
                    'cache-control' => 'max-age=3600',
                ],
            ]);
        } catch (GuzzleException $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        }
        if (! $this->ok($r)) throw UnableToWriteFile::atLocation($path, $this->alasan($r));
    }

    private function unduh(string $path, bool $stream)
    {
        try {
            $r = $this->http->get('object/authenticated/' . $this->bucket . '/' . $this->enc($path), ['stream' => $stream]);
        } catch (GuzzleException $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage(), $e);
        }
        if (! $this->ok($r)) throw UnableToReadFile::fromLocation($path, $this->alasan($r));
        return $r;
    }

    public function read(string $path): string
    {
        return (string) $this->unduh($path, false)->getBody();
    }

    public function readStream(string $path)
    {
        $tmp = fopen('php://temp', 'w+b');
        $body = $this->unduh($path, true)->getBody();
        while (! $body->eof()) fwrite($tmp, $body->read(65536));
        rewind($tmp);
        return $tmp;
    }

    public function delete(string $path): void
    {
        try {
            $r = $this->http->delete('object/' . $this->bucket, ['json' => ['prefixes' => [ltrim($path, '/')]]]);
        } catch (GuzzleException $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage(), $e);
        }
        if (! $this->ok($r) && $r->getStatusCode() !== 404) throw UnableToDeleteFile::atLocation($path, $this->alasan($r));
    }

    public function deleteDirectory(string $path): void
    {
        $semua = [];
        foreach ($this->listContents($path, true) as $item) {
            if ($item instanceof FileAttributes) $semua[] = $item->path();
        }
        if (! $semua) return;
        try {
            $r = $this->http->delete('object/' . $this->bucket, ['json' => ['prefixes' => $semua]]);
        } catch (GuzzleException $e) {
            throw UnableToDeleteDirectory::atLocation($path, $e->getMessage(), $e);
        }
        if (! $this->ok($r)) throw UnableToDeleteDirectory::atLocation($path, $this->alasan($r));
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Supabase Storage tidak punya folder sungguhan; folder terbentuk otomatis saat file diunggah.
    }

    public function setVisibility(string $path, string $visibility): void
    {
        throw UnableToSetVisibility::atLocation($path, 'Visibilitas diatur per bucket di dashboard Supabase.');
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, $this->public ? 'public' : 'private');
    }

    private function info(string $path): ResponseInterface
    {
        try {
            $r = $this->http->head('object/authenticated/' . $this->bucket . '/' . $this->enc($path));
        } catch (GuzzleException $e) {
            throw UnableToRetrieveMetadata::create($path, 'info', $e->getMessage(), $e);
        }
        if (! $this->ok($r)) throw UnableToRetrieveMetadata::create($path, 'info', $this->alasan($r));
        return $r;
    }

    public function mimeType(string $path): FileAttributes
    {
        $tipe = $this->mime->detectMimeTypeFromPath($path);
        if (! $tipe) {
            $tipe = explode(';', $this->info($path)->getHeaderLine('Content-Type'))[0] ?: null;
        }
        if (! $tipe) throw UnableToRetrieveMetadata::mimeType($path);
        return new FileAttributes($path, null, null, null, $tipe);
    }

    public function lastModified(string $path): FileAttributes
    {
        $waktu = strtotime($this->info($path)->getHeaderLine('Last-Modified')) ?: null;
        if (! $waktu) throw UnableToRetrieveMetadata::lastModified($path);
        return new FileAttributes($path, null, null, $waktu);
    }

    public function fileSize(string $path): FileAttributes
    {
        $ukuran = $this->info($path)->getHeaderLine('Content-Length');
        if ($ukuran === '') throw UnableToRetrieveMetadata::fileSize($path);
        return new FileAttributes($path, (int) $ukuran);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $awalan = trim($path, '/');
        $offset = 0;
        do {
            try {
                $r = $this->http->post('object/list/' . $this->bucket, ['json' => [
                    'prefix' => $awalan, 'limit' => 1000, 'offset' => $offset,
                    'sortBy' => ['column' => 'name', 'order' => 'asc'],
                ]]);
            } catch (GuzzleException $e) {
                throw UnableToListContents::atLocation($path, $deep, $e);
            }
            if (! $this->ok($r)) throw UnableToListContents::atLocation($path, $deep, new \RuntimeException($this->alasan($r)));
            $isi = json_decode((string) $r->getBody(), true) ?: [];
            foreach ($isi as $item) {
                $lengkap = ltrim(($awalan !== '' ? $awalan . '/' : '') . $item['name'], '/');
                if (empty($item['id'])) {                    // folder
                    yield new DirectoryAttributes($lengkap);
                    if ($deep) yield from $this->listContents($lengkap, true);
                } else {
                    yield new FileAttributes($lengkap, $item['metadata']['size'] ?? null, null,
                        isset($item['updated_at']) ? strtotime($item['updated_at']) : null, $item['metadata']['mimetype'] ?? null);
                }
            }
            $offset += 1000;
        } while (count($isi) === 1000);
    }

    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $r = $this->http->post('object/move', ['json' => ['bucketId' => $this->bucket, 'sourceKey' => $source, 'destinationKey' => $destination]]);
        } catch (GuzzleException $e) {
            throw UnableToMoveFile::fromLocationTo($source, $destination, $e);
        }
        if (! $this->ok($r)) throw UnableToMoveFile::because($this->alasan($r), $source, $destination);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $r = $this->http->post('object/copy', ['json' => ['bucketId' => $this->bucket, 'sourceKey' => $source, 'destinationKey' => $destination]]);
        } catch (GuzzleException $e) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $e);
        }
        if (! $this->ok($r)) throw UnableToCopyFile::because($this->alasan($r), $source, $destination);
    }
}
