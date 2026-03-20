<?php

interface CsvImporterInterface
{
    public static function postType(): string;

    public function getTermSlugs(int $postId, string $taxonomy): string;

    public function header(): array;

    public function data(): iterable;

    public static function isAllowed(): bool;

    public static function exportParam(): string;

    public static function dryRunParam(): string;

    public function toArray(): array;

    public function handle(): void;
}
