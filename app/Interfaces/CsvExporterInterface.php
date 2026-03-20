<?php

interface CsvExporterInterface
{
    public static function postType(): string;

    public function redirectUrl(): string;

    public function map(): array;

    public static function isAllowed(): bool;

    public static function importParam(): string;

    public static function dryRunParam(): string;

    public static function successParam(): string;

    public function handle(): void;
}
