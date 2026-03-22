<?php

namespace App\Error {
    function wp_die(string $message): never
    {
        throw new \RuntimeException($message);
    }
}

namespace {
    use App\Error\AppError;

    beforeEach(function () {
        $_SERVER['HTTP_HOST'] = 'localhost';
    });

    test('AppErrorは共通窓口としてwp_dieへ委譲する', function () {
        expect(fn () => AppError::abort(new RuntimeException('停止')))
            ->toThrow(RuntimeException::class, '停止');
    });

    test('AppErrorはthrowableのメッセージで停止できる', function () {
        expect(fn () => AppError::abort(new InvalidArgumentException('不正値')))
            ->toThrow(RuntimeException::class, '不正値');
    });
}
