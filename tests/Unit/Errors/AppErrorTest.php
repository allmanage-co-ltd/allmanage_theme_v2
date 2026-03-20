<?php

use App\Errors\AppError;
use App\Interfaces\ThemeErrorHandlerInterface;

final class FakeThemeErrorHandler implements ThemeErrorHandlerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function abort(string $message, array $context = []): never
    {
        throw new RuntimeException($message . '|' . json_encode($context, JSON_UNESCAPED_UNICODE));
    }
}

afterEach(function () {
    AppError::resetHandler();
});

test('AppErrorは差し替えたハンドラへ委譲できる', function () {
    AppError::setHandler(new FakeThemeErrorHandler());

    expect(fn () => AppError::abort('停止', ['scope' => 'test']))
        ->toThrow(RuntimeException::class, '停止|{"scope":"test"}');
});

test('AppErrorはthrowableからメッセージを引き継げる', function () {
    AppError::setHandler(new FakeThemeErrorHandler());

    expect(fn () => AppError::fromThrowable(new InvalidArgumentException('不正値'), ['scope' => 'csv']))
        ->toThrow(RuntimeException::class, '不正値|{"scope":"csv","exception":"InvalidArgumentException"}');
});
