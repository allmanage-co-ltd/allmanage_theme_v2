<?php

namespace App\Errors {
    function wp_die(string $message): never
    {
        throw new \RuntimeException($message);
    }
}

namespace {
    use App\Errors\AppError;

    test('AppErrorは共通窓口としてwp_dieへ委譲する', function () {
        expect(fn () => AppError::abort('停止'))->toThrow(RuntimeException::class, '停止');
    });

    test('AppErrorはthrowableのメッセージで停止できる', function () {
        expect(fn () => AppError::fromThrowable(new InvalidArgumentException('不正値')))
            ->toThrow(RuntimeException::class, '不正値');
    });
}
