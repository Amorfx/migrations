<?php

declare(strict_types=1);

namespace Doctrine\Migrations\Version;

use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Metadata\AvailableMigrationsList;
use Doctrine\Migrations\Metadata\ExecutedMigration;
use Doctrine\Migrations\Metadata\ExecutedMigrationsSet;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\MigrationsRepository;
use function array_filter;

/**
 * The MigrationPlanCalculator is responsible for calculating the plan for migrating from the current
 * version to another version.
 */
final class CurrentMigrationStatusCalculator implements MigrationStatusCalculator
{
    /** @var MigrationsRepository */
    private $migrationRepository;

    /** @var MetadataStorage */
    private $metadataStorage;

    public function __construct(MigrationsRepository $migrationRepository, MetadataStorage $metadataStorage)
    {
        $this->migrationRepository = $migrationRepository;
        $this->metadataStorage     = $metadataStorage;
    }

    public function getExecutedUnavailableMigrations() : ExecutedMigrationsSet
    {
        $executedMigrationsSet  = $this->metadataStorage->getExecutedMigrations();
        $availableMigrationsSet = $this->migrationRepository->getMigrations();

        return new ExecutedMigrationsSet(array_filter($executedMigrationsSet->getItems(), static function (ExecutedMigration $migrationInfo) use ($availableMigrationsSet) : bool {
            return ! $availableMigrationsSet->hasMigration($migrationInfo->getVersion());
        }));
    }

    public function getNewMigrations() : AvailableMigrationsList
    {
        $executedMigrationsSet  = $this->metadataStorage->getExecutedMigrations();
        $availableMigrationsSet = $this->migrationRepository->getMigrations();

        return new AvailableMigrationsList(array_filter($availableMigrationsSet->getItems(), static function (AvailableMigration $migrationInfo) use ($executedMigrationsSet) : bool {
            return ! $executedMigrationsSet->hasMigration($migrationInfo->getVersion());
        }));
    }
}
