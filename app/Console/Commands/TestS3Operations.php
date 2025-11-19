<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class TestS3Operations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-s3-operations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private string $localPath;

    private string $remotePath;

    private string $copyPath;

    private array $diskConfig;

    private int $fileSizeInMb;

    private AwsS3V3Adapter $disk;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mb = 1024 * 1024;

        $disks = [
            'r2',
//            's3',
        ];

        $operations = [
            // 'put',
            'copy',
        ];

        $mupThresholds = [
            null, // default
            // 5000 * $mb, // default for copy/move
            // 1000 * $mb,
            // 128 * $mb,
            64 * $mb,
            16 * $mb, // default for upload
        ];

        $concurrencies = [
            null, // default
            // 3, // default for upload
            // 5, // default for copy/move
            10,
            // 25,
            50,
            100,
            // 200,
        ];

        $fileSizesInMb = [
            // 2,
            // 5,
            // 10,
            // 50,
            // 100,
            // 200,
            1000,
        ];

        $results = [];
        file_put_contents('/tmp/result.txt', '');

        foreach ($disks as $diskName) {
            foreach ($fileSizesInMb as $fileSizeInMb) {
                foreach ($operations as $operation) {
                    foreach ($mupThresholds as $mupThreshold) {
                        foreach ($concurrencies as $concurrency) {

                            // --------------------------------------------------------
                            // skipping operations when they match the defaults (null)
                            // --------------------------------------------------------
                            if ($operation == 'put' && $mupThreshold == (16 * $mb)) {
                                continue;
                            }

                            if ($operation == 'put' && $concurrency == 3) {
                                continue;
                            }

                            if ($operation == 'copy' && ($mupThreshold == 5000 * $mb)) {
                                continue;
                            }

                            if ($operation == 'copy' && $concurrency == 5) {
                                continue;
                            }
                            // --------------------------------------------------------

                            // It wont use concurrency if the file is less than the threshold
                            if ((($fileSizeInMb * $mb) <= $mupThreshold) && $concurrency > 0) {
                                continue;
                            }

                            // attempts...
                            for ($i = 0; $i < 2; $i++) {
                                $config = config("filesystems.disks.{$diskName}");
                                unset($config['options']);

                                // Using default settings
                                config()->set("filesystems.disks.{$diskName}", $config);

                                if ($mupThreshold) {
                                    config()->set("filesystems.disks.{$diskName}.options.mup_threshold", $mupThreshold);
                                }

                                if ($concurrency) {
                                    config()->set("filesystems.disks.{$diskName}.options.concurrency", $concurrency);
                                }

                                $this->diskConfig = config("filesystems.disks.{$diskName}");
                                $this->localPath = "dummy-files/dummy-{$fileSizeInMb}MB.bin";
                                $this->remotePath = "livewire-tmp/{$fileSizeInMb}MB.bin";
                                $this->copyPath = "large-files/{$fileSizeInMb}MB-".str()->uuid().'.bin';
                                $this->fileSizeInMb = $fileSizeInMb;
                                $this->disk = Storage::build($this->diskConfig);

                                if ($operation == 'put') {
                                    $this->ensureLocalFileExists();

                                    if ($this->disk->fileExists($this->remotePath)) {
                                        $this->disk->delete($this->remotePath);
                                    }

                                    // Heavily depends on my internet connection
                                    $t0 = microtime(true);
                                    $this->disk->put($this->remotePath, fopen($this->localPath, 'r'));
                                    $t1 = microtime(true);
                                } elseif ($operation == 'copy') {
                                    $this->ensureRemoteFileExists();

                                    // Does not depend on my internet connection
                                    $t0 = microtime(true);
                                    $this->disk->copy($this->remotePath, $this->copyPath);
                                    $t1 = microtime(true);
                                }

                                $result = [
                                    'atttempt' => $i,
                                    'disk' => $diskName,
                                    'file_size_in_mb' => $fileSizeInMb,
                                    'operation' => $operation,
                                    'mup_threshold' => $mupThreshold ? $mupThreshold / $mb.'MB' : ($operation == 'put' ? 'default (16MB)' : 'default (5000MB)'),
                                    'concurrency' => $concurrency ?: ($operation == 'put' ? 'default (3)' : 'default (5)'),
                                    'elapsed_time' => round($t1 - $t0, 2),
                                ];

                                $results[] = $result;

                                $json = json_encode($result, JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                                file_put_contents('/tmp/result.txt', $json.PHP_EOL, FILE_APPEND);
                                $this->info($json);

                                if ($i == 1) {
                                    file_put_contents('/tmp/result.txt', PHP_EOL, FILE_APPEND);
                                    $this->info('');
                                }
                            }
                        }
                    }
                }
            }
        }

        dd($results);
    }

    private function ensureLocalFileExists()
    {
        if (file_exists($this->localPath)) {
            return;
        }

        $command = "dd if=/dev/urandom of={$this->localPath} bs=1M count={$this->fileSizeInMb}";
        exec($command);
    }

    private function ensureRemoteFileExists()
    {
        if ($this->disk->fileExists($this->remotePath)) {
            return;
        }

        $this->ensureLocalFileExists();

        $this->disk->put($this->remotePath, fopen($this->localPath, 'r'));
    }
}
