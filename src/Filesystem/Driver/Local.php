<?php
declare (strict_types = 1);

namespace Yng\Filesystem\Driver;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathNormalizer;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use League\Flysystem\WhitespacePathNormalizer;
use Yng\Filesystem\Driver;

class Local extends Driver
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        'root' => '',
    ];

    /**
     * @var PathPrefixer
     */
    protected $prefixer;

    /**
     * @var PathNormalizer
     */
    protected $normalizer;

    protected function createAdapter(): FilesystemAdapter
    {
        $visibility = PortableVisibilityConverter::fromArray(
            $this->config['permissions'] ?? [],
            $this->config['visibility'] ?? Visibility::PRIVATE
        );

        $links = ($this->config['links'] ?? null) === 'skip'
            ? LocalFilesystemAdapter::SKIP_LINKS
            : LocalFilesystemAdapter::DISALLOW_LINKS;

        return new LocalFilesystemAdapter(
            $this->config['root'],
            $visibility,
            $this->config['lock'] ?? LOCK_EX,
            $links
        );
    }

    protected function prefixer()
    {
        if (!$this->prefixer) {
            $this->prefixer = new PathPrefixer($this->config['root'], DIRECTORY_SEPARATOR);
        }
        return $this->prefixer;
    }

    protected function normalizer()
    {
        if (!$this->normalizer) {
            $this->normalizer = new WhitespacePathNormalizer();
        }
        return $this->normalizer;
    }

    /**
     * 获取文件访问地址
     * @param string $path 文件路径
     * @return string
     */
    public function url(string $path): string
    {
        $path = $this->normalizer()->normalizePath($path);

        if (isset($this->config['url'])) {
            return $this->concatPathToUrl($this->config['url'], $path);
        }
        return parent::url($path);
    }

    public function path(string $path): string
    {
        return $this->prefixer()->prefixPath($path);
    }
}
