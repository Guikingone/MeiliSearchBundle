<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Metadata;

use MeiliSearchBundle\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;

use function count;
use function file_get_contents;
use function sprintf;
use function strtr;
use function sys_get_temp_dir;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexMetadataRegistry implements IndexMetadataRegistryInterface
{
    private const FILE_PATH_PREFIX = '_ms_bundle_';

    private readonly string $path;

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly SerializerInterface $serializer,
        ?string $path = null
    ) {
        $this->path = $path ?: sys_get_temp_dir();
    }

    public function add(string $index, IndexMetadataInterface $metadata): void
    {
        if ($this->has($index)) {
            $this->override($index, $metadata);

            return;
        }

        $data = $this->serializer->serialize($metadata, 'json');

        $this->filesystem->dumpFile(sprintf('%s/%s.json', $this->getFilePath(), $index), $data);
    }

    public function override(string $index, IndexMetadataInterface $newConfiguration): void
    {
        if (!$this->has($index)) {
            $this->add($index, $newConfiguration);

            return;
        }

        $this->remove($index);
        $this->add($index, $newConfiguration);
    }

    public function get(string $index): IndexMetadataInterface
    {
        if (!$this->has($index)) {
            throw new InvalidArgumentException('The desired index does not exist');
        }

        return $this->serializer->deserialize(
            file_get_contents(sprintf('%s/%s.json', $this->getFilePath(), $index)),
            IndexMetadata::class,
            'json'
        );
    }

    public function remove(string $index): void
    {
        if (!$this->has($index)) {
            throw new InvalidArgumentException('The desired index does not exist');
        }

        $this->filesystem->remove(sprintf('%s/%s.json', $this->getFilePath(), $index));
    }

    public function has(string $index): bool
    {
        return $this->filesystem->exists(sprintf('%s/%s.json', $this->getFilePath(), $index));
    }

    public function clear(): void
    {
        if (!$this->filesystem->exists($this->getFilePath())) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($this->getFilePath());

        foreach ($finder as $file) {
            $this->remove(strtr($file->getFilename(), ['.json' => '']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if (!$this->filesystem->exists($this->getFilePath())) {
            return [];
        }

        $finder = new Finder();
        $finder->files()->in($this->getFilePath());

        $list = [];
        foreach ($finder as $file) {
            $index = strtr($file->getFilename(), ['.json' => '']);

            $list[$index] = $this->get($index);
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->toArray());
    }

    private function getFilePath(): string
    {
        return sprintf('%s/%s', $this->path, self::FILE_PATH_PREFIX);
    }
}
