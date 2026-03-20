<?php

namespace App\Interfaces;

interface CsvImporterInterface
{
    public static function postType(): string;

    public function redirectUrl(): string;

    public static function isAllowed(): bool;

    public static function importParam(): string;

    public static function dryRunParam(): string;

    public static function successParam(): string;

    public function handle(): void;
}
