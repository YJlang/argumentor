<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * 토론 입력 옵션의 단일 출처. DB ENUM 값(키) ↔ 화면 표시(라벨) 매핑.
 * 폼 렌더링과 입력 검증이 모두 이 정의를 사용한다.
 */
final class DebateOptions
{
    /** @var array<string, string> */
    public const STANCES = [
        'for'     => '찬성',
        'against' => '반대',
    ];

    /** @var array<string, string> */
    public const STYLES = [
        'logical'    => '논리적',
        'aggressive' => '공격적',
        'academic'   => '학술적',
    ];

    /** @var array<string, string> */
    public const LANGUAGES = [
        'ko' => '한국어',
        'en' => 'English',
        'ja' => '日本語',
        'zh' => '中文',
    ];

    public static function isValidStance(string $value): bool
    {
        return array_key_exists($value, self::STANCES);
    }

    public static function isValidStyle(string $value): bool
    {
        return array_key_exists($value, self::STYLES);
    }

    public static function isValidLanguage(string $value): bool
    {
        return array_key_exists($value, self::LANGUAGES);
    }

    /** AI가 취할 반대 입장의 라벨 (사용자 입장의 반대). */
    public static function opposingStanceLabel(string $userStance): string
    {
        return $userStance === 'for' ? self::STANCES['against'] : self::STANCES['for'];
    }
}
