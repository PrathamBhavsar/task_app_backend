<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Repository\ConfigRepositoryInterface;
use Domain\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;

class ConfigRepository implements ConfigRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function get(string $key): ?string
    {
        $config = $this->em->getRepository(Config::class)->findOneBy(['key' => $key]);
        return $config?->getValue();
    }

    public function set(string $key, string $value): void
    {
        $repo = $this->em->getRepository(Config::class);
        $config = $repo->findOneBy(['key' => $key]);

        if (!$config) {
            $config = new Config($key, $value);
            $this->em->persist($config);
        } else {
            $config->setValue($value);
        }

        $this->em->flush();
    }
}
