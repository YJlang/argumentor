<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * 성결대 SKU창의적인재전형 토론면접 스타일의 주제 풀.
 * 고등학생 수준의 시사·상식, 명확한 찬반 이분법, 제시문 없이 주제만 (docs/SUNGKYUL_INTERVIEW.md).
 */
final class SungkyulTopics
{
    /** @var array<int, string> */
    public const TOPICS = [
        '인터넷 실명제를 도입해야 한다',
        '난민 수용을 확대해야 한다',
        '반려동물 보유세를 도입해야 한다',
        '인공지능 창작물의 저작권을 인정해야 한다',
        '청소년의 SNS 사용을 법으로 제한해야 한다',
        '사형제도를 폐지해야 한다',
        '코로나 이후에도 대학 온라인 수업을 유지해야 한다',
        'CCTV 확대 설치는 사회 안전에 도움이 된다',
        '학교에서 학생의 휴대전화 사용을 전면 금지해야 한다',
        '유명인사에 대한 병역 특례를 허용해야 한다',
        '교내 채식 급식 의무화를 도입해야 한다',
        '인공지능 면접 도입은 채용 공정성에 도움이 된다',
        '기본소득제를 도입해야 한다',
        '동물 실험을 전면 금지해야 한다',
        '플라스틱 일회용품 사용을 법으로 전면 금지해야 한다',
    ];

    /**
     * 주제 추첨 (가상 추첨). index를 받아 결정적으로 고르거나, 미지정 시 시간 기반으로 고른다.
     */
    public static function draw(?int $seed = null): string
    {
        $count = count(self::TOPICS);
        $i = $seed ?? (int) (microtime(true) * 1000);

        return self::TOPICS[(($i % $count) + $count) % $count];
    }

    public static function isValid(string $topic): bool
    {
        return in_array($topic, self::TOPICS, true);
    }
}
