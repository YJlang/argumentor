<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * LLM 호출/파싱 과정에서 발생하는 오류.
 */
final class LlmException extends RuntimeException
{
}
