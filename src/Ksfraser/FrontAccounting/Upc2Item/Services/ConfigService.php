<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Services;

use ksfraser\FrontAccounting\Upc2Item\Contracts\ConfigServiceInterface;

/**
 * Module configuration service using gzip-compressed storage.
 * 
 * @BABOK Related: FR-UPCS-009
 */
class ConfigService implements ConfigServiceInterface
{
    /** @var string Path to config file */
    private $configPath;

    /** @var array<string, mixed> Cached config */
    private $config = [];

    public function __construct(string $moduleDir)
    {
        $this->configPath = $moduleDir . '/_init/config';
        $this->load();
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): bool
    {
        $this->config[$key] = $value;
        return $this->save();
    }

    /**
     * Load configuration from gzip file.
     * 
     * @return void
     */
    private function load(): void
    {
        if (!file_exists($this->configPath)) {
            if (!is_dir(dirname($this->configPath))) {
                mkdir(dirname($this->configPath), 0777, true);
            }
            $this->config = [
                'default_sales_type_id' => '1',
                'amazon_enabled' => '1',
                'ebay_enabled' => '1',
                'facebook_enabled' => '1',
                'search_timeout' => '15',
            ];
            return;
        }

        $data = file_get_contents($this->configPath);
        $decompressed = @gzinflate($data);
        if ($decompressed === false) {
            $decompressed = $data;
        }

        $this->config = [];
        foreach (explode("\n", $decompressed) as $line) {
            if (strpos($line, ':') !== false) {
                [$k, $v] = explode(':', $line, 2);
                $this->config[trim($k)] = trim($v);
            }
        }
    }

    /**
     * Save configuration to gzip file.
     * 
     * @return bool
     */
    private function save(): bool
    {
        $content = '';
        foreach ($this->config as $k => $v) {
            $content .= $k . ': ' . $v . "\n";
        }

        $compressed = gzdeflate($content, 9);
        return file_put_contents($this->configPath, $compressed) !== false;
    }
}
