<?php

namespace App\Interfaces;

interface CsvExporterInterface
{
    public static function postType(): string;

    public static function isAllowed(): bool;

    public static function exportParam(): string;

    public static function dryRunParam(): string;

    public function toArray(): array;

    public function handle(): void;
}
